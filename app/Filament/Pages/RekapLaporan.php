<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\AccountingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class RekapLaporan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Rekap Laporan';

    protected static ?string $slug = 'reports/rekap-laporan';

    protected static string $view = 'filament.pages.rekap-laporan';

    // Form state properties
    public ?string $start_date = null;
    public ?string $end_date = null;
    public bool $include_operational_expenses = false;

    // Report data
    public array $reportData = [];
    public array $ordersData = [];

    public static function canAccess(): bool
    {
        // Both Admin and Owner can access
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Periode')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport())
                            ->default(now()->startOfMonth()),
                        DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport())
                            ->default(now()->endOfMonth()),
                        Toggle::make('include_operational_expenses')
                            ->label('Tampilkan Beban Operasional (6xx)')
                            ->helperText('Kurangi laba kotor dengan beban operasional untuk menampilkan laba bersih')
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport())
                            ->visible(fn () => auth()->user()?->isOwner()),
                    ])
                    ->columns(3),
            ]);
    }

    public function generateReport(): void
    {
        $isOwner = auth()->user()?->isOwner() ?? false;
        
        // Get revenue (account 411)
        $revenueAccount = Account::where('code', '411')->first();
        $totalRevenue = 0;
        $revenues = [];
        
        if ($revenueAccount) {
            $amount = Transaction::where('account_id', $revenueAccount->id)
                ->active()
                ->dateRange($this->start_date, $this->end_date)
                ->credits()
                ->sum('amount');
            
            if ($amount > 0) {
                $revenues[] = [
                    'code' => $revenueAccount->code,
                    'name' => $revenueAccount->name,
                    'amount' => $amount,
                ];
                $totalRevenue = $amount;
            }
        }

        // Get HPP (account 511)
        $hppAccount = Account::where('code', '511')->first();
        $totalHPP = 0;
        $hppList = [];
        
        if ($hppAccount) {
            $amount = Transaction::where('account_id', $hppAccount->id)
                ->active()
                ->dateRange($this->start_date, $this->end_date)
                ->debits()
                ->sum('amount');
            
            if ($amount > 0) {
                $hppList[] = [
                    'code' => $hppAccount->code,
                    'name' => $hppAccount->name,
                    'amount' => $amount,
                ];
                $totalHPP = $amount;
            }
        }

        // Calculate Laba Kotor
        $labaKotor = $totalRevenue - $totalHPP;

        // Get operational expenses (accounts 6xx) - only for Owner with toggle enabled
        $operationalExpenses = [];
        $totalOperationalExpenses = 0;

        if ($isOwner && $this->include_operational_expenses) {
            $expenseAccounts = Account::where('type', 'expense')
                ->where('code', 'LIKE', '6%')
                ->get();

            foreach ($expenseAccounts as $account) {
                $amount = Transaction::where('account_id', $account->id)
                    ->active()
                    ->dateRange($this->start_date, $this->end_date)
                    ->debits()
                    ->sum('amount');
                
                if ($amount > 0) {
                    $operationalExpenses[] = [
                        'code' => $account->code,
                        'name' => $account->name,
                        'amount' => $amount,
                    ];
                    $totalOperationalExpenses += $amount;
                }
            }
        }

        // Calculate Laba Bersih (only if Owner with toggle enabled)
        $labaBersih = $labaKotor - $totalOperationalExpenses;

        // Get orders for the period
        $this->ordersData = Order::with('customer')
            ->whereBetween('order_date', [$this->start_date, $this->end_date])
            ->orderBy('order_date', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'order_number' => $order->order_number,
                    'order_date' => Carbon::parse($order->order_date)->format('d M Y'),
                    'customer_name' => $order->customer?->name ?? 'Guest',
                    'total_amount' => $order->total_amount,
                    'paid_amount' => $order->paid_amount,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                ];
            })
            ->toArray();

        // Calculate order statistics
        $totalOrders = count($this->ordersData);
        $paidOrders = collect($this->ordersData)->where('payment_status', 'paid')->count();
        $partialOrders = collect($this->ordersData)->where('payment_status', 'partial')->count();
        $unpaidOrders = collect($this->ordersData)->where('payment_status', 'unpaid')->count();

        $this->reportData = [
            'revenues' => $revenues,
            'totalRevenue' => $totalRevenue,
            'hppList' => $hppList,
            'totalHPP' => $totalHPP,
            'labaKotor' => $labaKotor,
            'operationalExpenses' => $operationalExpenses,
            'totalOperationalExpenses' => $totalOperationalExpenses,
            'labaBersih' => $labaBersih,
            'showOperationalExpenses' => $isOwner && $this->include_operational_expenses,
            'isOwner' => $isOwner,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'orderStats' => [
                'total' => $totalOrders,
                'paid' => $paidOrders,
                'partial' => $partialOrders,
                'unpaid' => $unpaidOrders,
            ],
        ];
    }

    public function filter(): void
    {
        $this->generateReport();
    }

    /**
     * Get header actions for the page.
     */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportPdf()),
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-table-cells')
                    ->action(fn () => $this->exportCsv()),
            ])
            ->label('Cetak Laporan')
            ->icon('heroicon-o-printer')
            ->button()
            ->color('success'),
        ];
    }

    public function exportPdf()
    {
        // Ensure report data is fresh
        $this->generateReport();
        
        $pdf = Pdf::loadView('reports.rekap-laporan-pdf', [
            'data' => $this->reportData,
            'orders' => $this->ordersData,
        ]);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'Rekap_Laporan_Mutiara_Rizki_' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    public function exportCsv()
    {
        // Ensure report data is fresh
        $this->generateReport();

        $filename = 'Rekap_Laporan_Mutiara_Rizki_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header info
            fputcsv($file, ['REKAP LAPORAN - PERCETAKAN MUTIARA RIZKI']);
            fputcsv($file, ['Periode: ' . Carbon::parse($this->start_date)->format('d M Y') . ' - ' . Carbon::parse($this->end_date)->format('d M Y')]);
            fputcsv($file, []);
            
            // Summary section
            fputcsv($file, ['RINGKASAN KEUANGAN']);
            fputcsv($file, ['Keterangan', 'Jumlah']);
            fputcsv($file, ['Total Omset (Pendapatan)', $this->reportData['totalRevenue']]);
            fputcsv($file, ['Total HPP Bahan Baku', $this->reportData['totalHPP']]);
            fputcsv($file, ['Laba Kotor', $this->reportData['labaKotor']]);
            
            if ($this->reportData['showOperationalExpenses']) {
                fputcsv($file, ['Total Beban Operasional', $this->reportData['totalOperationalExpenses']]);
                fputcsv($file, ['Laba Bersih', $this->reportData['labaBersih']]);
            }
            
            fputcsv($file, []);
            
            // Order statistics
            fputcsv($file, ['STATISTIK PESANAN']);
            fputcsv($file, ['Total Pesanan', $this->reportData['orderStats']['total']]);
            fputcsv($file, ['Lunas', $this->reportData['orderStats']['paid']]);
            fputcsv($file, ['Sebagian Dibayar', $this->reportData['orderStats']['partial']]);
            fputcsv($file, ['Belum Dibayar', $this->reportData['orderStats']['unpaid']]);
            fputcsv($file, []);
            
            // Orders detail
            fputcsv($file, ['DETAIL PESANAN']);
            fputcsv($file, ['No. Order', 'Tanggal', 'Pelanggan', 'Total', 'Dibayar', 'Status Pembayaran', 'Status Order']);
            
            foreach ($this->ordersData as $order) {
                fputcsv($file, [
                    $order['order_number'],
                    $order['order_date'],
                    $order['customer_name'],
                    $order['total_amount'],
                    $order['paid_amount'],
                    $order['payment_status'],
                    $order['status'],
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
