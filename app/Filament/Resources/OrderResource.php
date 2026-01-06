<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Services\AccountingService;
use App\Services\OrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Pesanan';

    protected static ?string $pluralModelLabel = 'Pesanan';

    /**
     * Static cache for products to prevent N+1 queries in repeater.
     * Now includes prices and price_types for fast lookup.
     */
    protected static array $cachedProducts = [];
    protected static array $cachedProductPrices = [];
    protected static array $cachedProductPriceTypes = [];

    /**
     * Get cached products for select options.
     */
    protected static function getCachedProducts(): array
    {
        if (empty(self::$cachedProducts)) {
            $products = Product::select('id', 'name', 'price', 'price_type')->get();
            self::$cachedProducts = $products->pluck('name', 'id')->toArray();
            self::$cachedProductPrices = $products->pluck('price', 'id')->toArray();
            self::$cachedProductPriceTypes = $products->pluck('price_type', 'id')->toArray();
        }
        return self::$cachedProducts;
    }

    /**
     * Get cached product price by ID.
     */
    protected static function getCachedProductPrice(int $productId): ?float
    {
        self::getCachedProducts(); // Ensure cache is populated
        $price = self::$cachedProductPrices[$productId] ?? null;
        return $price !== null ? (float) $price : null;
    }

    /**
     * Get cached product price_type by ID.
     */
    protected static function getCachedProductPriceType(int $productId): ?string
    {
        self::getCachedProducts(); // Ensure cache is populated
        return self::$cachedProductPriceTypes[$productId] ?? 'unit';
    }

    /**
     * Eager load relationships to prevent N+1 queries on table.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'items.materialUsages.material']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informasi Pesanan')
                            ->schema([
                                Forms\Components\TextInput::make('order_number')
                                    ->label('No. Pesanan')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->placeholder('Auto-generate'),
                                Forms\Components\Select::make('customer_id')
                                    ->label('Pelanggan')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Pelanggan')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('No. Telepon')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\Textarea::make('address')
                                            ->label('Alamat')
                                            ->rows(2),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Customer::create($data)->getKey();
                                    }),
                                Forms\Components\DatePicker::make('order_date')
                                    ->label('Tanggal Pesanan')
                                    ->required()
                                    ->default(now()),
                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Deadline'),
                                Forms\Components\Select::make('status')
                                    ->label('Status Pesanan')
                                    ->options([
                                        'pending' => 'Menunggu',
                                        'in_progress' => 'Dalam Proses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ])
                                    ->default('pending')
                                    ->required(),
                                Forms\Components\Select::make('production_status')
                                    ->label('Status Produksi')
                                    ->options([
                                        'queue' => 'Antrian',
                                        'process' => 'Proses',
                                        'done' => 'Selesai',
                                        'picked_up' => 'Diambil',
                                    ])
                                    ->default('queue')
                                    ->required(),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Detail Pesanan')
                            ->schema([
                                Forms\Components\Repeater::make('items')
                                    ->relationship()
                                    ->label('Item Pesanan')
                                    ->schema([
                                        Forms\Components\Select::make('product_id')
                                            ->label('Produk')
                                            ->options(fn () => self::getCachedProducts())
                                            ->searchable()
                                            ->required()
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                if ($state) {
                                                    $price = self::getCachedProductPrice((int) $state);
                                                    $priceType = self::getCachedProductPriceType((int) $state);
                                                    if ($price !== null) {
                                                        $set('unit_price', $price);
                                                    }
                                                    $set('temp_price_type', $priceType ?? 'unit');
                                                    // Clear dimensions when switching to unit-based product
                                                    if ($priceType === 'unit') {
                                                        $set('width', null);
                                                        $set('height', null);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        Forms\Components\Hidden::make('temp_price_type')
                                            ->default('unit')
                                            ->dehydrated(false),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live(debounce: 500),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->columnSpan(2)
                                            ->live(debounce: 500),
                                        Forms\Components\TextInput::make('width')
                                            ->label('Lebar (cm)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->visible(fn (Get $get) => $get('temp_price_type') === 'area')
                                            ->required(fn (Get $get) => $get('temp_price_type') === 'area')
                                            ->live(debounce: 500)
                                            ->helperText('Wajib untuk produk area'),
                                        Forms\Components\TextInput::make('height')
                                            ->label('Panjang (cm)')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->visible(fn (Get $get) => $get('temp_price_type') === 'area')
                                            ->required(fn (Get $get) => $get('temp_price_type') === 'area')
                                            ->live(debounce: 500)
                                            ->helperText('Wajib untuk produk area'),
                                        Forms\Components\Textarea::make('specifications')
                                            ->label('Spesifikasi')
                                            ->rows(1)
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('material')
                                            ->label('Bahan')
                                            ->placeholder('Art Paper, HVS, dll')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('finishing')
                                            ->label('Finishing')
                                            ->placeholder('Laminasi, Spot UV, dll')
                                            ->maxLength(100),
                                        Forms\Components\Select::make('binding_type')
                                            ->label('Jilid')
                                            ->options([
                                                'hardcover' => 'Hardcover',
                                                'softcover' => 'Softcover',
                                                'spiral' => 'Spiral',
                                                'staples' => 'Staples',
                                                'perfect_binding' => 'Perfect Binding',
                                            ])
                                            ->placeholder('Pilih jilid...'),
                                        Forms\Components\TextInput::make('finishing_cost')
                                            ->label('Biaya Finishing')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live(debounce: 500)
                                            ->helperText('Biaya tambahan finishing'),
                                        Forms\Components\Placeholder::make('subtotal_preview')
                                            ->label('Subtotal')
                                            ->content(function (Get $get) {
                                                $quantity = (int) ($get('quantity') ?? 0);
                                                $unitPrice = (float) ($get('unit_price') ?? 0);
                                                $finishingCost = (float) ($get('finishing_cost') ?? 0);
                                                $priceType = $get('temp_price_type') ?? 'unit';
                                                
                                                if ($priceType === 'area') {
                                                    $width = (float) ($get('width') ?? 0);
                                                    $height = (float) ($get('height') ?? 0);
                                                    $areaM2 = ($width * $height) / 10000;
                                                    $subtotal = $quantity * $unitPrice * $areaM2 + $finishingCost;
                                                } else {
                                                    $subtotal = $quantity * $unitPrice + $finishingCost;
                                                }
                                                
                                                return 'Rp ' . number_format($subtotal, 0, ',', '.');
                                            })
                                            ->columnSpan(2),
                                        
                                        // Material Usage for HPP Calculation
                                        Forms\Components\Section::make('Pemakaian Bahan (HPP)')
                                            ->schema([
                                                Forms\Components\Repeater::make('materialUsages')
                                                    ->relationship()
                                                    ->label('')
                                                    ->schema([
                                                        Forms\Components\Select::make('material_id')
                                                            ->label('Bahan')
                                                            ->options(Material::active()->pluck('name', 'id'))
                                                            ->searchable()
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function (Set $set, $state) {
                                                                if ($state) {
                                                                    $material = Material::find($state);
                                                                    if ($material) {
                                                                        $set('cost_per_unit', $material->cost_per_unit);
                                                                    }
                                                                }
                                                            })
                                                            ->columnSpan(2),
                                                        Forms\Components\TextInput::make('quantity_used')
                                                            ->label('Jumlah')
                                                            ->numeric()
                                                            ->required()
                                                            ->minValue(0.01)
                                                            ->live(debounce: 500),
                                                        Forms\Components\TextInput::make('cost_per_unit')
                                                            ->label('Harga/Unit')
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->disabled()
                                                            ->dehydrated(true),
                                                        Forms\Components\Placeholder::make('hpp_preview')
                                                            ->label('HPP')
                                                            ->content(fn (Get $get) => 'Rp ' . number_format(
                                                                ((float) ($get('quantity_used') ?? 0)) * ((float) ($get('cost_per_unit') ?? 0)),
                                                                0, ',', '.'
                                                            )),
                                                    ])
                                                    ->columns(5)
                                                    ->defaultItems(0)
                                                    ->addActionLabel('+ Tambah Bahan')
                                                    ->collapsible()
                                                    ->collapsed(),
                                            ])
                                            ->columnSpanFull()
                                            ->collapsible()
                                            ->collapsed(),
                                    ])
                                    ->columns(6)
                                    ->defaultItems(1)
                                    ->addActionLabel('+ Tambah Item')
                                    ->reorderable()
                                    ->collapsible()
                                    ->cloneable(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Pembayaran')
                            ->schema([
                                Forms\Components\Placeholder::make('total_biaya_display')
                                    ->label('Total Biaya')
                                    ->content(function (Get $get, ?Order $record) {
                                        // For existing records, use saved value; for new/editing, calculate from items
                                        $items = $get('items') ?? [];
                                        if (empty($items) && $record) {
                                            return 'Rp ' . number_format($record->total_amount, 0, ',', '.');
                                        }
                                        
                                        $total = collect($items)->sum(function ($item) {
                                            $quantity = (int) ($item['quantity'] ?? 0);
                                            $unitPrice = (float) ($item['unit_price'] ?? 0);
                                            $finishingCost = (float) ($item['finishing_cost'] ?? 0);
                                            $width = (float) ($item['width'] ?? 0);
                                            $height = (float) ($item['height'] ?? 0);
                                            
                                            // Check if area-based pricing
                                            if ($width > 0 && $height > 0) {
                                                $areaM2 = ($width * $height) / 10000;
                                                return $quantity * $unitPrice * $areaM2 + $finishingCost;
                                            }
                                            return $quantity * $unitPrice + $finishingCost;
                                        });
                                        
                                        return 'Rp ' . number_format($total, 0, ',', '.');
                                    }),
                                Forms\Components\TextInput::make('down_payment')
                                    ->label('Uang Muka (DP)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->helperText('Masukkan jumlah DP jika ada'),
                                Forms\Components\Placeholder::make('paid_amount_display')
                                    ->label('Total Dibayar')
                                    ->content(fn (?Order $record): string => $record ? 'Rp ' . number_format($record->paid_amount, 0, ',', '.') : 'Rp 0'),
                                Forms\Components\Placeholder::make('sisa_bayar_display')
                                    ->label('Sisa Bayar')
                                    ->content(function (Get $get, ?Order $record) {
                                        // Calculate total from items
                                        $items = $get('items') ?? [];
                                        $paidAmount = $record ? (float) $record->paid_amount : 0;
                                        
                                        if (empty($items) && $record) {
                                            $total = (float) $record->total_amount;
                                        } else {
                                            $total = collect($items)->sum(function ($item) {
                                                $quantity = (int) ($item['quantity'] ?? 0);
                                                $unitPrice = (float) ($item['unit_price'] ?? 0);
                                                $finishingCost = (float) ($item['finishing_cost'] ?? 0);
                                                $width = (float) ($item['width'] ?? 0);
                                                $height = (float) ($item['height'] ?? 0);
                                                
                                                if ($width > 0 && $height > 0) {
                                                    $areaM2 = ($width * $height) / 10000;
                                                    return $quantity * $unitPrice * $areaM2 + $finishingCost;
                                                }
                                                return $quantity * $unitPrice + $finishingCost;
                                            });
                                        }
                                        
                                        $remaining = max(0, $total - $paidAmount);
                                        return 'Rp ' . number_format($remaining, 0, ',', '.');
                                    }),
                                Forms\Components\Select::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        'unpaid' => 'Belum Bayar',
                                        'partial' => 'Dibayar Sebagian',
                                        'paid' => 'Lunas',
                                    ])
                                    ->default('unpaid')
                                    ->disabled(),
                            ]),

                        Forms\Components\Section::make('Catatan')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Catatan Tambahan')
                                    ->rows(4),
                            ]),

                        Forms\Components\Section::make('Total')
                            ->schema([
                                Forms\Components\Placeholder::make('total_amount_display')
                                    ->label('Total')
                                    ->content(function (Get $get, ?Order $record) {
                                        // Same logic as total_biaya_display for consistency
                                        $items = $get('items') ?? [];
                                        if (empty($items) && $record) {
                                            return 'Rp ' . number_format($record->total_amount, 0, ',', '.');
                                        }
                                        
                                        $total = collect($items)->sum(function ($item) {
                                            $quantity = (int) ($item['quantity'] ?? 0);
                                            $unitPrice = (float) ($item['unit_price'] ?? 0);
                                            $finishingCost = (float) ($item['finishing_cost'] ?? 0);
                                            $width = (float) ($item['width'] ?? 0);
                                            $height = (float) ($item['height'] ?? 0);
                                            
                                            if ($width > 0 && $height > 0) {
                                                $areaM2 = ($width * $height) / 10000;
                                                return $quantity * $unitPrice * $areaM2 + $finishingCost;
                                            }
                                            return $quantity * $unitPrice + $finishingCost;
                                        });
                                        
                                        return 'Rp ' . number_format($total, 0, ',', '.');
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                Tables\Columns\BadgeColumn::make('production_status')
                    ->label('Produksi')
                    ->colors([
                        'gray' => 'queue',
                        'info' => 'process',
                        'success' => 'done',
                        'primary' => 'picked_up',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'queue' => 'Antrian',
                        'process' => 'Proses',
                        'done' => 'Selesai',
                        'picked_up' => 'Diambil',
                        default => $state ?? '-',
                    }),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->colors([
                        'danger' => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        default => $state ?? '-',
                    }),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'unpaid' => 'Belum Bayar',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                    ]),
                Tables\Filters\SelectFilter::make('production_status')
                    ->label('Status Produksi')
                    ->options([
                        'queue' => 'Antrian',
                        'process' => 'Proses',
                        'done' => 'Selesai',
                        'picked_up' => 'Diambil',
                    ]),
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                    // Record Payment Action
                    Tables\Actions\Action::make('record_payment')
                        ->label('Catat Pembayaran')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (Order $record): bool => !$record->isVoided() && !$record->isFullyPaid())
                        ->form([
                            Forms\Components\Placeholder::make('info')
                                ->content(fn (Order $record): string => 
                                    "Total: Rp " . number_format($record->total_amount, 0, ',', '.') . 
                                    " | Dibayar: Rp " . number_format($record->paid_amount, 0, ',', '.') . 
                                    " | Sisa: Rp " . number_format($record->remaining_amount, 0, ',', '.')
                                ),
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah Pembayaran')
                                ->numeric()
                                ->required()
                                ->prefix('Rp')
                                ->default(fn (Order $record): float => $record->remaining_amount),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $accountingService = app(AccountingService::class);
                            $accountingService->recordPayment($record, (float) $data['amount']);
                            
                            Notification::make()
                                ->title('Pembayaran berhasil dicatat')
                                ->success()
                                ->send();
                        }),

                    // Record DP Action
                    Tables\Actions\Action::make('record_dp')
                        ->label('Catat Uang Muka')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->visible(fn (Order $record): bool => !$record->isVoided() && $record->down_payment <= 0 && $record->paid_amount <= 0)
                        ->form([
                            Forms\Components\Placeholder::make('info')
                                ->content(fn (Order $record): string => 
                                    "Total Pesanan: Rp " . number_format($record->total_amount, 0, ',', '.')
                                ),
                            Forms\Components\TextInput::make('amount')
                                ->label('Jumlah Uang Muka')
                                ->numeric()
                                ->required()
                                ->prefix('Rp'),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $accountingService = app(AccountingService::class);
                            $accountingService->recordDownPayment($record, (float) $data['amount']);
                            
                            Notification::make()
                                ->title('Uang muka berhasil dicatat')
                                ->success()
                                ->send();
                        }),

                    // Void Order Action
                    Tables\Actions\Action::make('void_order')
                        ->label('Batalkan Pesanan')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Order $record): bool => !$record->isVoided())
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Pesanan')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan pesanan ini? Jurnal balik akan dibuat untuk membatalkan transaksi keuangan.')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Alasan Pembatalan')
                                ->required(),
                        ])
                        ->action(function (Order $record, array $data): void {
                            $accountingService = app(AccountingService::class);
                            $accountingService->voidOrderTransactions($record, auth()->id(), $data['reason']);
                            
                            Notification::make()
                                ->title('Pesanan berhasil dibatalkan')
                                ->body('Jurnal balik telah dibuat.')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (Order $record): bool => $record->isVoided() || $record->paid_amount <= 0),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

