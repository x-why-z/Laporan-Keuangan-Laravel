<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class OrderStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    // Enable lazy loading for better initial page performance
    protected static bool $isLazy = true;

    /**
     * Get cached order statistics using a single optimized query.
     */
    protected function getStats(): array
    {
        // Cache stats for 60 seconds to reduce database load
        $stats = Cache::remember('order_stats_widget', 60, function () {
            return Order::active()
                ->selectRaw("
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid,
                    SUM(CASE WHEN payment_status = 'partial' THEN 1 ELSE 0 END) as partial_paid,
                    SUM(CASE WHEN production_status = 'queue' THEN 1 ELSE 0 END) as queue_count,
                    SUM(CASE WHEN production_status = 'process' THEN 1 ELSE 0 END) as process_count
                ")
                ->first();
        });

        return [
            Stat::make('Pesanan Pending', (int) ($stats->pending ?? 0))
                ->description('Menunggu diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Dalam Produksi', (int) ($stats->process_count ?? 0))
                ->description('Sedang dikerjakan')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),

            Stat::make('Antrian Produksi', (int) ($stats->queue_count ?? 0))
                ->description('Menunggu dikerjakan')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('gray'),

            Stat::make('Belum Lunas', (int) (($stats->unpaid ?? 0) + ($stats->partial_paid ?? 0)))
                ->description((int) ($stats->unpaid ?? 0) . ' belum bayar, ' . (int) ($stats->partial_paid ?? 0) . ' parsial')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
