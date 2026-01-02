<?php

namespace App\Filament\Pages;

use App\Models\Account;
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

    public function generateReport(): void
    {
        $accounts = Account::active()->get();

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
            ];
            $totalLiabilities += $account->balance;
        }

        $equity = [];
        $totalEquity = 0;
        foreach ($equityAccounts as $account) {
            $equity[] = [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $account->balance,
            ];
            $totalEquity += $account->balance;
        }

        $this->reportData = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquity,
            'date' => now()->format('d F Y'),
        ];
    }

    public function refresh(): void
    {
        $this->generateReport();
    }
}
