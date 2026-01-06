<?php

namespace App\Filament\Widgets;

use App\Services\AccountingService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class FinanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = true;
    
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $stats = Cache::remember('finance_stats_widget', 60, function () {
            $accountingService = app(AccountingService::class);
            
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();
            $prevMonthStart = now()->subMonth()->startOfMonth()->toDateString();
            $prevMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

            // Current month
            $currentOmset = $accountingService->getTotalRevenue($monthStart, $monthEnd);
            $currentHPP = $accountingService->getTotalHPP($monthStart, $monthEnd);
            $currentLabaKotor = $currentOmset - $currentHPP;
            
            // Previous month for comparison
            $prevOmset = $accountingService->getTotalRevenue($prevMonthStart, $prevMonthEnd);
            $prevHPP = $accountingService->getTotalHPP($prevMonthStart, $prevMonthEnd);
            $prevLabaKotor = $prevOmset - $prevHPP;
            
            // Calculate percentage changes
            $omsetChange = $prevOmset > 0 ? (($currentOmset - $prevOmset) / $prevOmset) * 100 : 0;
            $hppChange = $prevHPP > 0 ? (($currentHPP - $prevHPP) / $prevHPP) * 100 : 0;
            $labaChange = $prevLabaKotor != 0 ? (($currentLabaKotor - $prevLabaKotor) / abs($prevLabaKotor)) * 100 : 0;
            
            // Gross margin percentage
            $marginPercent = $currentOmset > 0 ? ($currentLabaKotor / $currentOmset) * 100 : 0;

            return [
                'omset' => $currentOmset,
                'hpp' => $currentHPP,
                'labaKotor' => $currentLabaKotor,
                'omsetChange' => $omsetChange,
                'hppChange' => $hppChange,
                'labaChange' => $labaChange,
                'marginPercent' => $marginPercent,
            ];
        });

        return [
            Stat::make('Omset Bulan Ini', 'Rp ' . number_format($stats['omset'], 0, ',', '.'))
                ->description($this->formatChange($stats['omsetChange']))
                ->descriptionIcon($stats['omsetChange'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart($this->getMiniChart('omset')),
                
            Stat::make('HPP Bulan Ini', 'Rp ' . number_format($stats['hpp'], 0, ',', '.'))
                ->description($this->formatChange($stats['hppChange'], true))
                ->descriptionIcon($stats['hppChange'] <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($stats['hppChange'] <= 0 ? 'success' : 'warning')
                ->chart($this->getMiniChart('hpp')),
                
            Stat::make('Laba Kotor', 'Rp ' . number_format($stats['labaKotor'], 0, ',', '.'))
                ->description(number_format($stats['marginPercent'], 1) . '% margin')
                ->descriptionIcon($stats['labaKotor'] >= 0 ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down')
                ->color($stats['labaKotor'] >= 0 ? 'success' : 'danger')
                ->chart($this->getMiniChart('laba')),
        ];
    }

    private function formatChange(float $change, bool $inverse = false): string
    {
        $prefix = $change >= 0 ? '+' : '';
        $suffix = ' vs bulan lalu';
        return $prefix . number_format($change, 1) . '%' . $suffix;
    }

    private function getMiniChart(string $type): array
    {
        // Return simple mini chart data (7 days trend)
        // In production, this would come from actual data
        return Cache::remember("mini_chart_{$type}", 300, function () {
            // Placeholder random data for visual effect
            return array_map(fn() => rand(60, 100), range(1, 7));
        });
    }
}
