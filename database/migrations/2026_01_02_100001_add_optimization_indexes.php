<?php

use Illuminate\Database\Migrations\Migration;
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
        // For SQLite, we need to check if index exists before creating
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Check existing indexes and create if not exists
            $this->createIndexIfNotExists('orders', 'order_number', 'orders_order_number_index');
            $this->createIndexIfNotExists('orders', 'payment_status', 'orders_payment_status_index');
            $this->createIndexIfNotExists('orders', 'production_status', 'orders_production_status_index');
            $this->createIndexIfNotExists('transactions', 'account_id', 'transactions_account_id_index');
            $this->createIndexIfNotExists('transactions', 'transaction_date', 'transactions_transaction_date_index');
            $this->createIndexIfNotExists('transactions', 'order_id', 'transactions_order_id_index');
        } else {
            // MySQL/MariaDB: Use native index creation
            DB::statement('CREATE INDEX IF NOT EXISTS orders_order_number_index ON orders (order_number)');
            DB::statement('CREATE INDEX IF NOT EXISTS orders_payment_status_index ON orders (payment_status)');
            DB::statement('CREATE INDEX IF NOT EXISTS orders_production_status_index ON orders (production_status)');
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_account_id_index ON transactions (account_id)');
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_transaction_date_index ON transactions (transaction_date)');
            DB::statement('CREATE INDEX IF NOT EXISTS transactions_order_id_index ON transactions (order_id)');
        }
    }

    /**
     * Create index if it doesn't exist (SQLite helper).
     */
    private function createIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        $indexes = DB::select("PRAGMA index_list('{$table}')");
        $indexNames = array_column($indexes, 'name');

        if (!in_array($indexName, $indexNames)) {
            DB::statement("CREATE INDEX {$indexName} ON {$table} ({$column})");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS orders_order_number_index');
            DB::statement('DROP INDEX IF EXISTS orders_payment_status_index');
            DB::statement('DROP INDEX IF EXISTS orders_production_status_index');
            DB::statement('DROP INDEX IF EXISTS transactions_account_id_index');
            DB::statement('DROP INDEX IF EXISTS transactions_transaction_date_index');
            DB::statement('DROP INDEX IF EXISTS transactions_order_id_index');
        } else {
            DB::statement('DROP INDEX IF EXISTS orders_order_number_index ON orders');
            DB::statement('DROP INDEX IF EXISTS orders_payment_status_index ON orders');
            DB::statement('DROP INDEX IF EXISTS orders_production_status_index ON orders');
            DB::statement('DROP INDEX IF EXISTS transactions_account_id_index ON transactions');
            DB::statement('DROP INDEX IF EXISTS transactions_transaction_date_index ON transactions');
            DB::statement('DROP INDEX IF EXISTS transactions_order_id_index ON transactions');
        }
    }
};
