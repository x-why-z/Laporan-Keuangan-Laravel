<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUsage extends Model
{
    use HasFactory;

    protected $table = 'material_usage';

    protected $fillable = [
        'order_item_id',
        'material_id',
        'quantity_used',
        'cost_per_unit',
        'total_cost',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Boot method to auto-calculate total cost.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($usage) {
            // Auto-calculate total_cost = quantity_used * cost_per_unit
            $usage->total_cost = $usage->quantity_used * $usage->cost_per_unit;
        });
    }

    /**
     * Get the order item that owns this usage.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the material that was used.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
