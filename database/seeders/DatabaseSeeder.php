<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in order
        $this->call([
            AccountSeeder::class,       // Chart of Accounts
            RoleSeeder::class,          // Roles and Users
            ProductSeeder::class,       // Katalog Produk (with price_type)
            MaterialSeeder::class,      // Bahan Baku (Raw Materials)
            OpeningBalanceSeeder::class, // Saldo Awal (Opening Balance)
            CustomerSeeder::class,      // Pelanggan Dummy
            DummyTransactionSeeder::class, // Transaksi Dummy
        ]);
    }
}
