<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Flag to skip automatic total calculation (for batch operations).
     */
    public static bool $skipTotalCalculation = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'width',
        'height',
        'subtotal',
        'specifications',
        'material',
        'finishing',
        'binding_type',
        'finishing_cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'finishing_cost' => 'decimal:2',
    ];

    /**
     * Boot method for auto-calculating subtotal.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($item) {
            // Calculate subtotal: quantity * unit_price * (width * height if applicable)
            $subtotal = $item->quantity * $item->unit_price;
            
            // If width and height are provided, multiply by area (for items sold by area)
            if ($item->width && $item->height) {
                $areaCm2 = $item->width * $item->height;
                $areaM2 = $areaCm2 / 10000; // Convert cm² to m²
                $subtotal = $item->quantity * $item->unit_price * $areaM2;
            }
            
            // Add finishing cost to subtotal
            $subtotal += (float) ($item->finishing_cost ?? 0);
            
            $item->subtotal = $subtotal;
        });

        // Note: calculateTotal() is now called once after all items are saved
        // via Filament's afterSave hook in OrderResource, not per-item.
        // This prevents N+1 updates when saving orders with many items.
    }

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for the item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all material usages for this order item.
     */
    public function materialUsages(): HasMany
    {
        return $this->hasMany(MaterialUsage::class);
    }

    /**
     * Calculate total HPP for this item based on material usage.
     */
    public function getTotalHppAttribute(): float
    {
        return (float) $this->materialUsages()->sum('total_cost');
    }
}
