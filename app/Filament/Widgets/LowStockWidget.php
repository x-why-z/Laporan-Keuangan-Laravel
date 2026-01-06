<?php

namespace App\Filament\Widgets;

use App\Models\Material;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Peringatan Stok Rendah';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Material::query()
                    ->active()
                    ->lowStock()
                    ->orderBy('stock_quantity', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Bahan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stok Saat Ini')
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Minimum Stok'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge(),
                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Harga Beli')
                    ->money('IDR'),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Tambah Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (Material $record) => route('filament.admin.resources.materials.index', [
                        'tableSearch' => $record->name,
                    ])),
            ])
            ->emptyStateHeading('Semua stok aman!')
            ->emptyStateDescription('Tidak ada bahan dengan stok di bawah minimum.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
