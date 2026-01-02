<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add additional indexes for frequently queried columns to improve performance.
     */
    public function up(): void
    {
        // Orders table indexes
        if (!$this->indexExists('orders', 'orders_order_number_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('order_number', 'orders_order_number_index');
            });
        }

        if (!$this->indexExists('orders', 'orders_payment_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('payment_status', 'orders_payment_status_index');
            });
        }

        if (!$this->indexExists('orders', 'orders_production_status_index')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->index('production_status', 'orders_production_status_index');
            });
        }

        // Transactions table indexes
        if (!$this->indexExists('transactions', 'transactions_account_id_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('account_id', 'transactions_account_id_index');
            });
        }

        if (!$this->indexExists('transactions', 'transactions_transaction_date_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('transaction_date', 'transactions_transaction_date_index');
            });
        }

        if (!$this->indexExists('transactions', 'transactions_order_id_index')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('order_id', 'transactions_order_id_index');
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            return in_array($indexName, array_column($indexes, 'name'));
        }

        // MySQL/MariaDB
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_order_number_index');
            $table->dropIndex('orders_payment_status_index');
            $table->dropIndex('orders_production_status_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_account_id_index');
            $table->dropIndex('transactions_transaction_date_index');
            $table->dropIndex('transactions_order_id_index');
        });
    }
};

