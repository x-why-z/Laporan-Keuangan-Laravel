<?php

namespace App\Filament\Pages;

use App\Models\OrderItem;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\DB;

class ProductMarginAnalysis extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Analisis Margin Produk';

    protected static ?string $slug = 'reports/product-margin';

    protected static string $view = 'filament.pages.product-margin-analysis';

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
        // Get sales data grouped by product
        $salesData = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereNull('orders.voided_at')
            ->whereBetween('orders.order_date', [$this->start_date, $this->end_date])
            ->select(
                'products.id',
                'products.name',
                'products.unit',
                'products.price as base_price',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name', 'products.unit', 'products.price')
            ->orderByDesc('total_revenue')
            ->get();

        $products = [];
        $totalRevenue = 0;

        foreach ($salesData as $item) {
            // Calculate average selling price
            $avgPrice = $item->total_qty > 0 ? $item->total_revenue / $item->total_qty : 0;
            
            // Calculate margin (difference between avg selling price and base price)
            $marginPerUnit = $avgPrice - $item->base_price;
            $marginPercent = $item->base_price > 0 ? ($marginPerUnit / $item->base_price) * 100 : 0;
            $totalMargin = $marginPerUnit * $item->total_qty;

            $products[] = [
                'name' => $item->name,
                'unit' => $item->unit,
                'basePrice' => $item->base_price,
                'avgPrice' => $avgPrice,
                'totalQty' => $item->total_qty,
                'totalRevenue' => $item->total_revenue,
                'totalOrders' => $item->total_orders,
                'marginPerUnit' => $marginPerUnit,
                'marginPercent' => $marginPercent,
                'totalMargin' => $totalMargin,
            ];

            $totalRevenue += $item->total_revenue;
        }

        // Calculate contribution percentage
        foreach ($products as &$product) {
            $product['contribution'] = $totalRevenue > 0 
                ? ($product['totalRevenue'] / $totalRevenue) * 100 
                : 0;
        }

        $this->reportData = [
            'products' => $products,
            'totalRevenue' => $totalRevenue,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
        ];
    }

    public function filter(): void
    {
        $this->generateReport();
    }
}
