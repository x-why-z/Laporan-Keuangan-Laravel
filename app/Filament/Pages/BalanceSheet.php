<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Services\AccountingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;

class BalanceSheet extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Neraca';

    protected static ?string $slug = 'reports/balance-sheet';

    protected static string $view = 'filament.pages.balance-sheet';

    public static function canAccess(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }

    public array $reportData = [];

    public function mount(): void
    {
        $this->generateReport();
    }

    /**
     * Get tooltip descriptions for account codes.
     */
    protected function getAccountTooltips(): array
    {
        return [
            '111' => 'Saldo uang tunai dan bank yang siap digunakan untuk operasional harian.',
            '112' => 'Tagihan kepada pelanggan atas pesanan yang sudah selesai dikerjakan namun belum lunas.',
            '113' => 'Total nilai bahan baku (kertas, tinta, banner) yang masih tersedia di gudang.',
            '211' => 'Uang muka (DP) yang diterima, namun pesanan masih dalam proses produksi (Hutang Jasa).',
            '212' => 'Kewajiban pembayaran kepada supplier atas pembelian bahan secara kredit.',
            '311' => 'Investasi awal atau setoran modal pribadi pemilik.',
            '312' => 'Akumulasi profit atau loss dari operasional harian secara otomatis.',
        ];
    }

    public function generateReport(): void
    {
        $accounts = Account::active()->get();
        $tooltips = $this->getAccountTooltips();

        // Group accounts by type
        $assetAccounts = $accounts->where('type', 'asset');
        $liabilityAccounts = $accounts->where('type', 'liability');
        $equityAccounts = $accounts->where('type', 'equity');

        $assets = [];
        $totalAssets = 0;
        foreach ($assetAccounts as $account) {
            $assets[] = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $account->balance,
                'tooltip' => $tooltips[$account->code] ?? null,
            ];
            $totalAssets += $account->balance;
        }

        $liabilities = [];
        $totalLiabilities = 0;
        foreach ($liabilityAccounts as $account) {
            $liabilities[] = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $account->balance,
                'tooltip' => $tooltips[$account->code] ?? null,
            ];
            $totalLiabilities += $account->balance;
        }

        // Calculate Net Profit dynamically for Laba Ditahan (312)
        $accountingService = app(AccountingService::class);
        $netProfit = $accountingService->calculateNetProfit();

        $equity = [];
        $totalEquity = 0;
        foreach ($equityAccounts as $account) {
            $balance = $account->balance;
            
            // For Laba Ditahan (312), use calculated net profit instead of static balance
            if ($account->code === '312') {
                $balance = $netProfit;
            }
            
            $equity[] = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
                'isDynamic' => $account->code === '312',
                'tooltip' => $tooltips[$account->code] ?? null,
            ];
            $totalEquity += $balance;
        }

        $this->reportData = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquity,
            'netProfit' => $netProfit,
            'date' => now()->format('d F Y'),
        ];
    }

    public function refresh(): void
    {
        $this->generateReport();
    }

    /**
     * Get header actions for the page.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePdfReport')
                ->label('Generate Laporan')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Ensure report data is fresh
                    $this->generateReport();
                    
                    $pdf = Pdf::loadView('reports.balance-sheet-pdf', [
                        'data' => $this->reportData,
                        'date' => $this->reportData['date'] ?? now()->format('d F Y'),
                    ]);

                    $pdf->setPaper('a4', 'portrait');

                    $filename = 'Laporan_Neraca_Mutiara_Rizki_' . now()->format('Y-m-d') . '.pdf';

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $filename
                    );
                }),
        ];
    }
}
