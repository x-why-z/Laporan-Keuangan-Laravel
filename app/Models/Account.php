<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all transactions for the account.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope for active accounts only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific account type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get account by code.
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    /**
     * Update balance based on transaction type.
     */
    public function updateBalance(string $transactionType, float $amount): void
    {
        // Untuk asset dan expense: debit = tambah, credit = kurang
        // Untuk liability, equity, revenue: debit = kurang, credit = tambah
        $isDebitNormal = in_array($this->type, ['asset', 'expense']);
        
        if ($transactionType === 'debit') {
            $this->balance += $isDebitNormal ? $amount : -$amount;
        } else {
            $this->balance += $isDebitNormal ? -$amount : $amount;
        }
        
        $this->save();
    }

    /**
     * Reverse balance update (for void transactions).
     */
    public function reverseBalance(string $transactionType, float $amount): void
    {
        // Kebalikan dari updateBalance
        $oppositeType = $transactionType === 'debit' ? 'credit' : 'debit';
        $this->updateBalance($oppositeType, $amount);
    }
}
