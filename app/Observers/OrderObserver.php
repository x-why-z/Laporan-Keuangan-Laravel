<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\AccountingService;
use App\Services\InventoryService;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status changed to 'completed' and HPP has not been recorded yet
        if ($order->wasChanged('status') && 
            $order->status === 'completed' && 
            !$order->hpp_recorded) {
            
            // 0. Ensure HPP is calculated
            $order->calculateHPP();

            // 1. Deduct stock (Inventory)
            $inventoryService = app(InventoryService::class);
            $inventoryService->recordOrderMaterialUsage($order);
            
            // 2. Journal HPP (Accounting)
            $accountingService = app(AccountingService::class);
            $accountingService->recordHPP($order);
        }
    }

    /**
     * Handle the Order "deleting" event (BEFORE deletion).
     * Reverse account balances and delete transactions to maintain financial integrity.
     * This ensures Balance Sheet and P&L remain accurate.
     */
    public function deleting(Order $order): void
    {
        // Get ALL transactions linked to this order (including voided ones for cleanup)
        $transactions = $order->transactions()->with('account')->get();
        
        // Reverse each transaction's effect on account balances
        foreach ($transactions as $transaction) {
            // Only reverse non-voided transactions
            if (!$transaction->is_void && $transaction->account) {
                // Reverse the balance: if it was debit, we need to credit (and vice versa)
                $transaction->account->reverseBalance($transaction->type, (float) $transaction->amount);
            }
        }
        
        // Delete all transactions linked to this order
        $order->transactions()->delete();
    }
}

