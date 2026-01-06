<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Bahan Baku';
    protected static ?string $pluralModelLabel = 'Bahan Baku';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Bahan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('unit')
                            ->label('Satuan')
                            ->placeholder('lembar, box, meter, liter')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Harga Beli per Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Minimum Stok')
                            ->numeric()
                            ->default(0)
                            ->helperText('Peringatan akan muncul jika stok di bawah angka ini'),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Stok Saat Ini')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false) // Jangan kirim ke controller/model saat save
                            ->helperText('Stok dikelola melalui menu pembelian/penyesuaian'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn (Material $record) => $record->isLowStock() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn (Builder $query) => $query->lowStock()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Action Tambah Stok (Pembelian)
                Tables\Actions\Action::make('add_stock')
                    ->label('Tambah Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Jumlah Masuk')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Harga Beli Baru')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(fn (Material $record) => $record->cost_per_unit)
                            ->helperText('Akan mengupdate harga beli terakhir'),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->placeholder('Pembelian dari Supplier X'),
                    ])
                    ->action(function (Material $record, array $data) {
                        $inventoryService = app(\App\Services\InventoryService::class);
                        $inventoryService->recordPurchase(
                            $record,
                            (float) $data['quantity'],
                            (float) $data['cost_per_unit'],
                            $data['description']
                        );
                        
                        // Opsional: Jurnal pembelian bahan (Debit Persediaan, Kredit Kas)
                        // Untuk simplicity, kita anggap pembelian tunai dari Kas
                        $accountingService = app(\App\Services\AccountingService::class);
                        $totalCost = (float) $data['quantity'] * (float) $data['cost_per_unit'];
                        $accountingService->recordMaterialPurchase($totalCost, "Pembelian Bahan: " . $record->name);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Stok berhasil ditambahkan')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
