<?php

namespace App\Filament\Pages;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountingService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ProfitLossReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Laporan Laba Rugi';

    protected static ?string $slug = 'reports/profit-loss';

    protected static string $view = 'filament.pages.profit-loss-report';

    public static function canAccess(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }

    public ?string $start_date = null;
    public ?string $end_date = null;
    public array $reportData = [];

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
                            ->default(now()->startOfMonth()),
                        DatePicker::make('end_date')
                            ->label('Tanggal Akhir')
                            ->required()
                            ->default(now()->endOfMonth()),
                    ])
                    ->columns(2),
            ]);
    }

    public function generateReport(): void
    {
        // Get revenue accounts
        $revenueAccounts = Account::where('type', 'revenue')->get();
        $expenseAccounts = Account::where('type', 'expense')->get();

        $revenues = [];
        $totalRevenue = 0;

        foreach ($revenueAccounts as $account) {
            $amount = Transaction::where('account_id', $account->id)
                ->active()
                ->dateRange($this->start_date, $this->end_date)
                ->credits()
                ->sum('amount');
            
            if ($amount > 0) {
                $revenues[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalRevenue += $amount;
            }
        }

        $expenses = [];
        $totalExpense = 0;

        foreach ($expenseAccounts as $account) {
            $amount = Transaction::where('account_id', $account->id)
                ->active()
                ->dateRange($this->start_date, $this->end_date)
                ->debits()
                ->sum('amount');
            
            if ($amount > 0) {
                $expenses[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => $amount,
                ];
                $totalExpense += $amount;
            }
        }

        $this->reportData = [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netProfit' => $totalRevenue - $totalExpense,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ];
    }

    public function filter(): void
    {
        $this->generateReport();
    }
}
