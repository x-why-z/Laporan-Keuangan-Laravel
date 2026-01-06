<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceSheetTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
        
        // Seed accounts
        $this->seed(\Database\Seeders\AccountSeeder::class);
    }

    /**
     * Test that Net Profit equals Revenue minus Expense.
     */
    public function test_net_profit_calculation(): void
    {
        // Create revenue transaction (Credit to 411)
        $revenueAccount = Account::findByCode('411');
        Transaction::create([
            'account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 500000,
            'description' => 'Test Revenue',
            'transaction_date' => now()->toDateString(),
        ]);
        $revenueAccount->updateBalance('credit', 500000);

        // Create expense transaction (Debit to 511 HPP)
        $expenseAccount = Account::findByCode('511');
        Transaction::create([
            'account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 200000,
            'description' => 'Test HPP',
            'transaction_date' => now()->toDateString(),
        ]);
        $expenseAccount->updateBalance('debit', 200000);

        // Calculate net profit: 500000 - 200000 = 300000
        $netProfit = $this->accountingService->calculateNetProfit();
        
        $this->assertEquals(300000, $netProfit);
    }

    /**
     * Test that owner capital recording creates balanced entries.
     */
    public function test_owner_capital_creates_balanced_entries(): void  
    {
        $transactions = $this->accountingService->recordOwnerCapital(1000000, 'Setoran Modal Awal');
        
        $this->assertCount(2, $transactions);
        
        // Check Kas increased
        $kasAccount = Account::findByCode('111');
        $this->assertEquals(1000000, $kasAccount->balance);
        
        // Check Modal increased
        $modalAccount = Account::findByCode('311');
        $this->assertEquals(1000000, $modalAccount->balance);
    }

    /**
     * Test balance sheet is balanced after order payment.
     */
    public function test_balance_sheet_balanced_after_payment(): void
    {
        // Record owner capital first
        $this->accountingService->recordOwnerCapital(1000000);
        
        // Create revenue (simulating order payment) using AccountingService patterns
        $kasAccount = Account::findByCode('111');
        $revenueAccount = Account::findByCode('411');
        
        $referenceNumber = 'TEST-' . uniqid();
        
        Transaction::create([
            'reference_number' => $referenceNumber . '-D',
            'account_id' => $kasAccount->id,
            'type' => 'debit',
            'amount' => 500000,
            'description' => 'Payment for order',
            'transaction_date' => now()->toDateString(),
        ]);
        $kasAccount->updateBalance('debit', 500000);
        
        Transaction::create([
            'reference_number' => $referenceNumber . '-C',
            'account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 500000,
            'description' => 'Revenue from order',
            'transaction_date' => now()->toDateString(),
        ]);
        $revenueAccount->updateBalance('credit', 500000);

        // Calculate totals
        $totalAssets = Account::where('type', 'asset')->sum('balance');
        $totalLiabilities = Account::where('type', 'liability')->sum('balance');
        $modalPemilik = Account::findByCode('311')->balance;
        $netProfit = $this->accountingService->calculateNetProfit();
        
        $totalEquity = $modalPemilik + $netProfit;
        
        // Assets should equal Liabilities + Equity
        $this->assertEquals($totalAssets, $totalLiabilities + $totalEquity);
    }
}
