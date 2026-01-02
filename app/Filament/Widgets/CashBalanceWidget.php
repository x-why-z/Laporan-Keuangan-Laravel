<?php

namespace App\Filament\Widgets;

use App\Services\AccountingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class CashBalanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // Enable lazy loading for better initial page performance
    protected static bool $isLazy = true;
    
    // Increased from 30s to 60s to reduce server load
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Cache all accounting stats for 60 seconds
        $stats = Cache::remember('cash_balance_widget', 60, function () {
            $accountingService = app(AccountingService::class);
            
            $todayStart = now()->startOfDay()->toDateString();
            $todayEnd = now()->endOfDay()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();

            return [
                'cashBalance' => $accountingService->getCashBalance(),
                'todayRevenue' => $accountingService->getTotalRevenue($todayStart, $todayEnd),
                'monthRevenue' => $accountingService->getTotalRevenue($monthStart, $monthEnd),
                'monthProfit' => $accountingService->getProfitLoss($monthStart, $monthEnd),
            ];
        });

        return [
            Stat::make('Saldo Kas', 'Rp ' . number_format($stats['cashBalance'], 0, ',', '.'))
                ->description('Saldo kas saat ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Pendapatan Hari Ini', 'Rp ' . number_format($stats['todayRevenue'], 0, ',', '.'))
                ->description('Total pendapatan hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
                
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($stats['monthRevenue'], 0, ',', '.'))
                ->description('Total pendapatan bulan berjalan')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('Laba Bulan Ini', 'Rp ' . number_format($stats['monthProfit'], 0, ',', '.'))
                ->description($stats['monthProfit'] >= 0 ? 'Laba bersih' : 'Rugi bersih')
                ->descriptionIcon($stats['monthProfit'] >= 0 ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down')
                ->color($stats['monthProfit'] >= 0 ? 'success' : 'danger'),
        ];
    }
}

