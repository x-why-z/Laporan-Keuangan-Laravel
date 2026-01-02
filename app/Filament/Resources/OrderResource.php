<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Customer;
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
     * Now includes prices for fast lookup.
     */
    protected static array $cachedProducts = [];
    protected static array $cachedProductPrices = [];

    /**
     * Get cached products for select options.
     */
    protected static function getCachedProducts(): array
    {
        if (empty(self::$cachedProducts)) {
            $products = Product::select('id', 'name', 'price')->get();
            self::$cachedProducts = $products->pluck('name', 'id')->toArray();
            self::$cachedProductPrices = $products->pluck('price', 'id')->toArray();
        }
        return self::$cachedProducts;
    }

    /**
     * Get cached product price by ID.
     */
    protected static function getCachedProductPrice(int $productId): ?float
    {
        self::getCachedProducts(); // Ensure cache is populated
        return self::$cachedProductPrices[$productId] ?? null;
    }

    /**
     * Eager load relationships to prevent N+1 queries on table.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'items']);
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
                                                    if ($price !== null) {
                                                        $set('unit_price', $price);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp'),
                                        Forms\Components\TextInput::make('width')
                                            ->label('Lebar (cm)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->nullable(),
                                        Forms\Components\TextInput::make('height')
                                            ->label('Panjang (cm)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->nullable(),
                                        Forms\Components\Textarea::make('specifications')
                                            ->label('Spesifikasi')
                                            ->rows(1)
                                            ->columnSpan(2),
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
                                Forms\Components\TextInput::make('down_payment')
                                    ->label('Uang Muka (DP)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),
                                Forms\Components\Placeholder::make('paid_amount_display')
                                    ->label('Total Dibayar')
                                    ->content(fn (?Order $record): string => $record ? 'Rp ' . number_format($record->paid_amount, 0, ',', '.') : 'Rp 0'),
                                Forms\Components\Placeholder::make('remaining_display')
                                    ->label('Sisa Bayar')
                                    ->content(fn (?Order $record): string => $record ? 'Rp ' . number_format($record->remaining_amount, 0, ',', '.') : 'Rp 0'),
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
                                    ->content(fn (?Order $record): string => $record ? 'Rp ' . number_format($record->total_amount, 0, ',', '.') : 'Rp 0'),
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

