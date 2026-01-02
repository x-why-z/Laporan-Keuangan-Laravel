<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'order_id',
        'account_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'is_void',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'is_void' => 'boolean',
        'voided_at' => 'datetime',
    ];

    /**
     * Boot method for auto-generating reference number.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference_number)) {
                $transaction->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /**
     * Generate unique reference number.
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'JRN';
        $date = now()->format('Ymd');
        $lastTransaction = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction 
            ? (int) substr($lastTransaction->reference_number, -4) + 1 
            : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the order that owns the transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the account that owns the transaction.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who voided the transaction.
     */
    public function voidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * Scope for non-voided transactions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_void', false);
    }

    /**
     * Scope for transactions within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for debit transactions.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for credit transactions.
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Void this transaction.
     */
    public function void(?int $userId = null, ?string $reason = null): bool
    {
        if ($this->is_void) {
            return false;
        }

        $this->update([
            'is_void' => true,
            'voided_by' => $userId,
            'voided_at' => now(),
            'void_reason' => $reason,
        ]);

        // Reverse the account balance
        $this->account->reverseBalance($this->type, $this->amount);

        return true;
    }
}
