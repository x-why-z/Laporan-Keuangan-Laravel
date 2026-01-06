<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\Account;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueVsCogsChart extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Omset vs HPP';
    
    protected static ?int $sort = 3;
    
    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = '120s';

    public ?string $filter = '6months';

    protected function getFilters(): ?array
    {
        return [
            '7days' => '7 Hari Terakhir',
            '30days' => '30 Hari Terakhir',
            '3months' => '3 Bulan Terakhir',
            '6months' => '6 Bulan Terakhir',
            '12months' => '12 Bulan Terakhir',
        ];
    }

    protected function getData(): array
    {
        $cacheKey = 'revenue_vs_cogs_chart_' . $this->filter;
        
        return Cache::remember($cacheKey, 300, function () {
            $data = $this->getChartData();
            
            return [
                'datasets' => [
                    [
                        'label' => 'Omset (Pendapatan)',
                        'data' => $data['omset'],
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'borderWidth' => 2,
                    ],
                    [
                        'label' => 'HPP (Beban Bahan)',
                        'data' => $data['hpp'],
                        'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                        'borderColor' => 'rgb(239, 68, 68)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => $data['labels'],
            ];
        });
    }

    protected function getChartData(): array
    {
        $revenueAccount = Account::findByCode('411'); // Pendapatan Jasa Cetak
        $hppAccount = Account::findByCode('511'); // Beban HPP Bahan Baku
        
        $periods = $this->getPeriods();
        
        $omset = [];
        $hpp = [];
        $labels = [];
        
        foreach ($periods as $period) {
            $labels[] = $period['label'];
            
            // Get revenue for period
            $omsetValue = 0;
            if ($revenueAccount) {
                $omsetValue = Transaction::active()
                    ->dateRange($period['start'], $period['end'])
                    ->where('account_id', $revenueAccount->id)
                    ->credits()
                    ->sum('amount');
            }
            $omset[] = round($omsetValue, 0);
            
            // Get HPP for period
            $hppValue = 0;
            if ($hppAccount) {
                $hppValue = Transaction::active()
                    ->dateRange($period['start'], $period['end'])
                    ->where('account_id', $hppAccount->id)
                    ->debits()
                    ->sum('amount');
            }
            $hpp[] = round($hppValue, 0);
        }
        
        return [
            'labels' => $labels,
            'omset' => $omset,
            'hpp' => $hpp,
        ];
    }

    protected function getPeriods(): array
    {
        $periods = [];
        
        switch ($this->filter) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $periods[] = [
                        'label' => $date->format('d M'),
                        'start' => $date->startOfDay()->toDateString(),
                        'end' => $date->endOfDay()->toDateString(),
                    ];
                }
                break;
                
            case '30days':
                // Group by week
                for ($i = 4; $i >= 0; $i--) {
                    $endDate = now()->subWeeks($i)->endOfWeek();
                    $startDate = now()->subWeeks($i)->startOfWeek();
                    $periods[] = [
                        'label' => 'Mg ' . $startDate->format('d/m'),
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                    ];
                }
                break;
                
            case '3months':
                for ($i = 2; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $periods[] = [
                        'label' => $date->translatedFormat('M Y'),
                        'start' => $date->startOfMonth()->toDateString(),
                        'end' => $date->endOfMonth()->toDateString(),
                    ];
                }
                break;
                
            case '6months':
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $periods[] = [
                        'label' => $date->translatedFormat('M'),
                        'start' => $date->startOfMonth()->toDateString(),
                        'end' => $date->endOfMonth()->toDateString(),
                    ];
                }
                break;
                
            case '12months':
            default:
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $periods[] = [
                        'label' => $date->translatedFormat('M'),
                        'start' => $date->startOfMonth()->toDateString(),
                        'end' => $date->endOfMonth()->toDateString(),
                    ];
                }
                break;
        }
        
        return $periods;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                        }",
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(1) + ' jt';
                        }",
                    ],
                ],
            ],
        ];
    }
}
