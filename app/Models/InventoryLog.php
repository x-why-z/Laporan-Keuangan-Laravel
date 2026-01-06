<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'type',
        'quantity',
        'cost_per_unit',
        'reference_type',
        'reference_id',
        'description',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_per_unit' => 'decimal:2',
    ];

    /**
     * Get the material for this log.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Get the user who created this log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for incoming stock.
     */
    public function scopeIncoming($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for outgoing stock.
     */
    public function scopeOutgoing($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Get the reference model (polymorphic-like resolution).
     */
    public function getReference(): ?Model
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }

        return match ($this->reference_type) {
            'order' => Order::find($this->reference_id),
            'order_item' => OrderItem::find($this->reference_id),
            default => null,
        };
    }

    /**
     * Check if this is an incoming (purchase/adjustment in) log.
     */
    public function isIncoming(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if this is an outgoing (usage/adjustment out) log.
     */
    public function isOutgoing(): bool
    {
        return $this->type === 'out';
    }
}
