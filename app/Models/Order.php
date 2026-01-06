<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'due_date',
        'status',
        'production_status',
        'total_amount',
        'total_hpp',
        'hpp_recorded',
        'down_payment',
        'paid_amount',
        'payment_status',
        'notes',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'order_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_hpp' => 'decimal:2',
        'hpp_recorded' => 'boolean',
        'down_payment' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    /**
     * Boot method for auto-generating order number.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? (int) substr($lastOrder->order_number, -4) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate and update total amount from order items.
     */
    public function calculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('subtotal');
        $this->save();
    }

    /**
     * Calculate and update total HPP from order items.
     */
    public function calculateHPP(): void
    {
        $this->total_hpp = $this->items->sum(function ($item) {
            return $item->total_hpp;
        });
        $this->save();
    }

    /**
     * Get remaining amount to be paid.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    /**
     * Check if order is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is voided.
     */
    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus(): void
    {
        if ($this->paid_amount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }
        $this->save();
    }

    /**
     * Get the customer that owns the order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get all items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all transactions for the order.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user who voided the order.
     */
    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Scope for non-voided orders.
     */
    public function scopeActive($query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('voided_at');
    }

    /**
     * Scope for orders by payment status.
     */
    public function scopePaymentStatus($query, string $status): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope for orders by production status.
     */
    public function scopeProductionStatus($query, string $status): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('production_status', $status);
    }
}

