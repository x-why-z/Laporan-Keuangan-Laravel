<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\AccountCodes;
use App\Models\Account;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Create a single journal entry with balance update.
     *
     * @param string $accountCode The account code (use AccountCodes constants)
     * @param string $type 'debit' or 'credit'
     * @param float $amount Transaction amount
     * @param string $description Transaction description
     * @param string $referenceNumber Base reference number
     * @param int|null $orderId Associated order ID
     * @param string $referenceSuffix Suffix to add to reference number
     * @return Transaction|null Created transaction or null if account not found
     */
    private function createJournalEntry(
        string $accountCode,
        string $type,
        float $amount,
        string $description,
        string $referenceNumber,
        ?int $orderId = null,
        string $referenceSuffix = ''
    ): ?Transaction {
        $account = Account::findByCode($accountCode);
        if (!$account) {
            return null;
        }

        $transaction = Transaction::create([
            'reference_number' => $referenceNumber . $referenceSuffix,
            'order_id' => $orderId,
            'account_id' => $account->id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
        ]);

        $account->updateBalance($type, $amount);

        return $transaction;
    }

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
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'debit',
                $amount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Pendapatan Diterima Dimuka
            $transaction = $this->createJournalEntry(
                AccountCodes::PENDAPATAN_DITERIMA_DIMUKA,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                $order->id,
                '-C'
            );
            if ($transaction) {
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
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'debit',
                $amount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Jika ada DP sebelumnya, transfer dari Pendapatan Diterima Dimuka ke Pendapatan
            // PENTING: Cek apakah DP sudah ditransfer sebelumnya (untuk mencegah double-debit pada pembayaran bertahap)
            $dpAmount = $order->down_payment;
            $hasTransferredDP = $order->transactions()
                ->active()
                ->where('reference_number', 'like', '%-DD')
                ->exists();

            if ($dpAmount > 0 && !$hasTransferredDP) {
                // Debit Pendapatan Diterima Dimuka
                $transaction = $this->createJournalEntry(
                    AccountCodes::PENDAPATAN_DITERIMA_DIMUKA,
                    'debit',
                    (float) $dpAmount,
                    "Transfer DP ke Pendapatan - {$order->order_number}",
                    $referenceNumber,
                    $order->id,
                    '-DD'
                );
                if ($transaction) {
                    $transactions[] = $transaction;
                }

                // Kredit Pendapatan Jasa Cetak untuk DP
                $transaction = $this->createJournalEntry(
                    AccountCodes::PENDAPATAN_JASA_CETAK,
                    'credit',
                    (float) $dpAmount,
                    "Pengakuan Pendapatan dari DP - {$order->order_number}",
                    $referenceNumber,
                    $order->id,
                    '-PDP'
                );
                if ($transaction) {
                    $transactions[] = $transaction;
                }
            }

            // Kredit Pendapatan Jasa Cetak untuk pembayaran ini
            $transaction = $this->createJournalEntry(
                AccountCodes::PENDAPATAN_JASA_CETAK,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                $order->id,
                '-P'
            );
            if ($transaction) {
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
                $transaction->account->updateBalance($reversingType, (float) $transaction->amount);
                
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
        $kasAccount = Account::findByCode(AccountCodes::KAS);
        return $kasAccount ? (float) $kasAccount->balance : 0;
    }

    /**
     * Get total revenue for date range.
     */
    public function getTotalRevenue(string $startDate, string $endDate): float
    {
        return (float) Transaction::active()
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
        return (float) Transaction::active()
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

    /**
     * Record HPP (Harga Pokok Penjualan) journal entries when order is completed.
     *
     * Jurnal HPP:
     * - Debit Beban HPP (511)
     * - Kredit Persediaan Bahan (113)
     *
     * @param Order $order
     * @return array
     */
    public function recordHPP(Order $order): array
    {
        $hppAmount = (float) $order->total_hpp;

        if ($hppAmount <= 0 || $order->hpp_recorded) {
            return [];
        }

        return DB::transaction(function () use ($order, $hppAmount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "HPP Pesanan #{$order->order_number}";

            // Debit Beban HPP (511) - Menambah beban
            $transaction = $this->createJournalEntry(
                AccountCodes::BEBAN_HPP,
                'debit',
                $hppAmount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Persediaan Bahan (113) - Mengurangi aset
            $transaction = $this->createJournalEntry(
                AccountCodes::PERSEDIAAN_BAHAN,
                'credit',
                $hppAmount,
                $description,
                $referenceNumber,
                $order->id,
                '-INV'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Mark HPP as recorded
            $order->update(['hpp_recorded' => true]);

            return $transactions;
        });
    }

    /**
     * Record material purchase journal entries.
     *
     * Jurnal Pembelian Bahan:
     * - Debit Persediaan Bahan (113)
     * - Kredit Kas (111)
     *
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function recordMaterialPurchase(float $amount, string $description = 'Pembelian Bahan Baku'): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($amount, $description) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();

            // Debit Persediaan Bahan (113) - Menambah aset
            $transaction = $this->createJournalEntry(
                AccountCodes::PERSEDIAAN_BAHAN,
                'debit',
                $amount,
                $description,
                $referenceNumber
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Kas (111) - Mengurangi kas
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                null,
                '-KAS'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Get total HPP for date range.
     */
    public function getTotalHPP(string $startDate, string $endDate): float
    {
        $hppAccount = Account::findByCode(AccountCodes::BEBAN_HPP);
        if (!$hppAccount) {
            return 0;
        }

        return (float) Transaction::active()
            ->dateRange($startDate, $endDate)
            ->where('account_id', $hppAccount->id)
            ->debits()
            ->sum('amount');
    }

    /**
     * Calculate total Net Profit (Laba Bersih) from all transactions.
     * This is used for Balance Sheet to display Laba Ditahan (312) dynamically.
     * 
     * Formula: Total Revenue (Credits) - Total Expenses (Debits)
     * 
     * @return float
     */
    public function calculateNetProfit(): float
    {
        $totalRevenue = (float) Transaction::active()
            ->whereHas('account', fn($q) => $q->where('type', 'revenue'))
            ->credits()
            ->sum('amount');

        $totalExpense = (float) Transaction::active()
            ->whereHas('account', fn($q) => $q->where('type', 'expense'))
            ->debits()
            ->sum('amount');

        return $totalRevenue - $totalExpense;
    }

    /**
     * Record owner capital investment (Setoran Modal).
     *
     * Jurnal Modal Pemilik:
     * - Debit Kas (111)
     * - Kredit Modal Pemilik (311)
     *
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function recordOwnerCapital(float $amount, string $description = 'Setoran Modal Pemilik'): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($amount, $description) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();

            // Debit Kas (111) - Menambah aset
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'debit',
                $amount,
                $description,
                $referenceNumber
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Modal Pemilik (311) - Menambah ekuitas
            $transaction = $this->createJournalEntry(
                AccountCodes::MODAL_PEMILIK,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                null,
                '-MOD'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Record receivable journal entries when order is completed but unpaid.
     *
     * Jurnal Piutang:
     * - Debit Piutang Usaha (112)
     * - Kredit Pendapatan Jasa Cetak (411)
     *
     * @param Order $order
     * @return array
     */
    public function recordReceivable(Order $order): array
    {
        $amount = (float) $order->total_amount;

        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($order, $amount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "Piutang Pesanan #{$order->order_number}";

            // Debit Piutang Usaha (112)
            $transaction = $this->createJournalEntry(
                AccountCodes::PIUTANG_USAHA,
                'debit',
                $amount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Pendapatan Jasa Cetak (411)
            $transaction = $this->createJournalEntry(
                AccountCodes::PENDAPATAN_JASA_CETAK,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                $order->id,
                '-REV'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Record full payment journal entries for cash payment.
     *
     * Jurnal Pembayaran Tunai:
     * - Debit Kas (111)
     * - Kredit Pendapatan Jasa Cetak (411)
     *
     * @param Order $order
     * @return array
     */
    public function recordFullPayment(Order $order): array
    {
        $amount = (float) $order->total_amount;

        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($order, $amount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "Pembayaran Tunai Pesanan #{$order->order_number}";

            // Debit Kas (111)
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'debit',
                $amount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Pendapatan Jasa Cetak (411)
            $transaction = $this->createJournalEntry(
                AccountCodes::PENDAPATAN_JASA_CETAK,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                $order->id,
                '-REV'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Record operational expense journal entries.
     *
     * Jurnal Beban Operasional:
     * - Debit Akun Beban (611/612/613/etc)
     * - Kredit Kas (111)
     *
     * @param string $expenseAccountCode Account code for expense (611, 612, etc)
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function recordExpense(string $expenseAccountCode, float $amount, string $description = 'Beban Operasional'): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($expenseAccountCode, $amount, $description) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();

            // Debit Expense Account
            $transaction = $this->createJournalEntry(
                $expenseAccountCode,
                'debit',
                $amount,
                $description,
                $referenceNumber
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Kas (111)
            $transaction = $this->createJournalEntry(
                AccountCodes::KAS,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                null,
                '-KAS'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Record credit material purchase journal entries.
     *
     * Jurnal Pembelian Kredit:
     * - Debit Persediaan Bahan (113)
     * - Kredit Hutang Usaha (212)
     *
     * @param float $amount
     * @param string $description
     * @return array
     */
    public function recordCreditPurchase(float $amount, string $description = 'Pembelian Bahan Baku (Kredit)'): array
    {
        if ($amount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($amount, $description) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();

            // Debit Persediaan Bahan (113)
            $transaction = $this->createJournalEntry(
                AccountCodes::PERSEDIAAN_BAHAN,
                'debit',
                $amount,
                $description,
                $referenceNumber
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Hutang Usaha (212)
            $transaction = $this->createJournalEntry(
                AccountCodes::HUTANG_USAHA,
                'credit',
                $amount,
                $description,
                $referenceNumber,
                null,
                '-HUT'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }

    /**
     * Record HPP with custom amount (for seeder/testing purposes).
     *
     * Jurnal HPP:
     * - Debit Beban HPP (511)
     * - Kredit Persediaan Bahan (113)
     *
     * @param Order $order
     * @param float|null $customAmount Optional custom amount to override order's total_hpp
     * @return array
     */
    public function recordHPPWithAmount(Order $order, float $customAmount): array
    {
        if ($customAmount <= 0) {
            return [];
        }

        return DB::transaction(function () use ($order, $customAmount) {
            $transactions = [];
            $referenceNumber = Transaction::generateReferenceNumber();
            $description = "HPP Pesanan #{$order->order_number}";

            // Debit Beban HPP (511)
            $transaction = $this->createJournalEntry(
                AccountCodes::BEBAN_HPP,
                'debit',
                $customAmount,
                $description,
                $referenceNumber,
                $order->id
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            // Kredit Persediaan Bahan (113)
            $transaction = $this->createJournalEntry(
                AccountCodes::PERSEDIAAN_BAHAN,
                'credit',
                $customAmount,
                $description,
                $referenceNumber,
                $order->id,
                '-INV'
            );
            if ($transaction) {
                $transactions[] = $transaction;
            }

            return $transactions;
        });
    }
}

