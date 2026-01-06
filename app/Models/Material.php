<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'cost_per_unit',
        'stock_quantity',
        'min_stock',
        'description',
        'is_active',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all usage records for this material.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class);
    }

    /**
     * Get all inventory logs for this material.
     */
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Scope for active materials only.
     */
    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock materials.
     */
    public function scopeLowStock($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    /**
     * Check if material is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Check if there is enough stock for usage.
     */
    public function hasEnoughStock(float $quantity): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Adjust stock quantity.
     * 
     * @param string $type 'in' for adding, 'out' for subtracting
     * @param float $quantity Amount to adjust
     */
    public function adjustStock(string $type, float $quantity): void
    {
        if ($type === 'in') {
            $this->stock_quantity += $quantity;
        } else {
            $this->stock_quantity -= $quantity;
        }
        $this->save();
    }

    /**
     * Update cost per unit (for FIFO/weighted average, can be extended).
     */
    public function updateCost(float $newCost): void
    {
        $this->cost_per_unit = $newCost;
        $this->save();
    }
}
