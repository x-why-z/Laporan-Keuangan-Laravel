<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Record journal entries for down payment (DP).
     * 
     * Skenario DP:
     * - Debit Kas (111)
     * - Kredit Pendapatan Diterima Dimuka (211)
     */
    public function recordDownPayment(Order $order, float $amount): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($order, $amount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "DP Pesanan #{$order->order_number}";

            // Debit Kas
            $kasAccount = Account::findByCode('111');
            if ($kasAccount) {
                $transaction = Transaction::create([
                    'reference_number' => $referenceNumber,
                    'order_id' => $order->id,
                    'account_id' => $kasAccount->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => $description,
                    'transaction_date' => now()->toDateString(),
                ]);
                $kasAccount->updateBalance('debit', $amount);
                $transactions[] = $transaction;
            }

            // Kredit Pendapatan Diterima Dimuka
            $pendapatanDDAccount = Account::findByCode('211');
            if ($pendapatanDDAccount) {
                $transaction = Transaction::create([
                    'reference_number' => $referenceNumber . '-C',
                    'order_id' => $order->id,
                    'account_id' => $pendapatanDDAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => $description,
                    'transaction_date' => now()->toDateString(),
                ]);
                $pendapatanDDAccount->updateBalance('credit', $amount);
                $transactions[] = $transaction;
            }

            // Update order
            $order->update([
                'down_payment' => $amount,
                'paid_amount' => $order->paid_amount + $amount,
            ]);
            $order->updatePaymentStatus();

            return $transactions;
        });
    }

    /**
     * Record journal entries for payment/pelunasan.
     * 
     * Skenario Pelunasan:
     * - Debit Kas (111)
     * - Kredit Piutang Usaha (112) atau Pendapatan Jasa Cetak (411)
     * - Debit Pendapatan Diterima Dimuka (211) jika ada DP sebelumnya
     * - Kredit Pendapatan Jasa Cetak (411) untuk mengakui pendapatan
     */
    public function recordPayment(Order $order, float $amount): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($order, $amount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "Pelunasan Pesanan #{$order->order_number}";

            // Debit Kas
            $kasAccount = Account::findByCode('111');
            if ($kasAccount) {
                $transaction = Transaction::create([
                    'reference_number' => $referenceNumber,
                    'order_id' => $order->id,
                    'account_id' => $kasAccount->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => $description,
                    'transaction_date' => now()->toDateString(),
                ]);
                $kasAccount->updateBalance('debit', $amount);
                $transactions[] = $transaction;
            }

            // Jika ada DP sebelumnya, transfer dari Pendapatan Diterima Dimuka ke Pendapatan
            $dpAmount = $order->down_payment;
            if ($dpAmount > 0) {
                $pendapatanDDAccount = Account::findByCode('211');
                $pendapatanAccount = Account::findByCode('411');

                if ($pendapatanDDAccount && $pendapatanAccount) {
                    // Debit Pendapatan Diterima Dimuka
                    $transaction = Transaction::create([
                        'reference_number' => $referenceNumber . '-DD',
                        'order_id' => $order->id,
                        'account_id' => $pendapatanDDAccount->id,
                        'type' => 'debit',
                        'amount' => $dpAmount,
                        'description' => "Transfer DP ke Pendapatan - {$order->order_number}",
                        'transaction_date' => now()->toDateString(),
                    ]);
                    $pendapatanDDAccount->updateBalance('debit', $dpAmount);
                    $transactions[] = $transaction;

                    // Kredit Pendapatan Jasa Cetak untuk DP
                    $transaction = Transaction::create([
                        'reference_number' => $referenceNumber . '-PDP',
                        'order_id' => $order->id,
                        'account_id' => $pendapatanAccount->id,
                        'type' => 'credit',
                        'amount' => $dpAmount,
                        'description' => "Pengakuan Pendapatan dari DP - {$order->order_number}",
                        'transaction_date' => now()->toDateString(),
                    ]);
                    $pendapatanAccount->updateBalance('credit', $dpAmount);
                    $transactions[] = $transaction;
                }
            }

            // Kredit Pendapatan Jasa Cetak untuk pembayaran ini
            $pendapatanAccount = Account::findByCode('411');
            if ($pendapatanAccount) {
                $transaction = Transaction::create([
                    'reference_number' => $referenceNumber . '-P',
                    'order_id' => $order->id,
                    'account_id' => $pendapatanAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => $description,
                    'transaction_date' => now()->toDateString(),
                ]);
                $pendapatanAccount->updateBalance('credit', $amount);
                $transactions[] = $transaction;
            }

            // Update order
            $order->update([
                'paid_amount' => $order->paid_amount + $amount,
            ]);
            $order->updatePaymentStatus();

            return $transactions;
        });
    }

    /**
     * Void order and create reversing journal entries.
     * 
     * Jurnal Balik: membalik semua jurnal yang terkait dengan order ini
     */
    public function voidOrderTransactions(Order $order, ?int $userId = null, ?string $reason = null): array
    {
        return DB::transaction(function () use ($order, $userId, $reason) {
            $reversedTransactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            
            // Get all active transactions for this order
            $activeTransactions = $order->transactions()->active()->get();

            foreach ($activeTransactions as $transaction) {
                // Void the original transaction
                $transaction->void($userId, $reason);

                // Create reversing entry
                $reversingType = $transaction->type === 'debit' ? 'credit' : 'debit';
                $reversingTransaction = Transaction::create([
                    'reference_number' => $referenceNumber . '-REV-' . $transaction->id,
                    'order_id' => $order->id,
                    'account_id' => $transaction->account_id,
                    'type' => $reversingType,
                    'amount' => $transaction->amount,
                    'description' => "VOID: {$transaction->description}",
                    'transaction_date' => now()->toDateString(),
                ]);

                // Update account balance with reversing entry
                $transaction->account->updateBalance($reversingType, $transaction->amount);
                
                $reversedTransactions[] = $reversingTransaction;
            }

            // Update order as voided
            $order->update([
                'voided_at' => now(),
                'voided_by' => $userId,
                'void_reason' => $reason,
                'status' => 'cancelled',
            ]);

            return $reversedTransactions;
        });
    }

    /**
     * Get cash balance (Saldo Kas).
     */
    public function getCashBalance(): float
    {
        $kasAccount = Account::findByCode('111');
        return $kasAccount ? (float) $kasAccount->balance : 0;
    }

    /**
     * Get total revenue for date range.
     */
    public function getTotalRevenue(string $startDate, string $endDate): float
    {
        return Transaction::active()
            ->dateRange($startDate, $endDate)
            ->whereHas('account', fn($q) => $q->where('type', 'revenue'))
            ->credits()
            ->sum('amount');
    }

    /**
     * Get total expenses for date range.
     */
    public function getTotalExpenses(string $startDate, string $endDate): float
    {
        return Transaction::active()
            ->dateRange($startDate, $endDate)
            ->whereHas('account', fn($q) => $q->where('type', 'expense'))
            ->debits()
            ->sum('amount');
    }

    /**
     * Get profit/loss for date range.
     */
    public function getProfitLoss(string $startDate, string $endDate): float
    {
        return $this->getTotalRevenue($startDate, $endDate) - $this->getTotalExpenses($startDate, $endDate);
    }

    /**
     * Get balance sheet data.
     */
    public function getBalanceSheet(): array
    {
        $accounts = Account::active()->get();

        return [
            'assets' => $accounts->where('type', 'asset')->sum('balance'),
            'liabilities' => $accounts->where('type', 'liability')->sum('balance'),
            'equity' => $accounts->where('type', 'equity')->sum('balance'),
            'accounts' => $accounts->groupBy('type'),
        ];
    }
}
