<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Services\AccountingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates integrated dummy transactions to simulate
     * a complete business operational cycle for the printing business.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $accountingService = app(AccountingService::class);

            // 1. Persiapan Data Pelanggan
            $customer1 = Customer::firstOrCreate(['name' => 'Budi Sudarsono'], ['phone' => '08123456789']);
            $customer2 = Customer::firstOrCreate(['name' => 'Siska Amelia'], ['phone' => '08771234567']);
            $customer3 = Customer::firstOrCreate(['name' => 'PT Maju Jaya'], ['phone' => '0219876543']);

            // Check if dummy orders already exist to make seeder idempotent
            $existingDummyOrder = Order::where('notes', 'LIKE', '%[DUMMY-SEEDER]%')->first();
            if ($existingDummyOrder) {
                $this->command->warn('⚠️  Dummy orders already exist. Skipping DummyTransactionSeeder.');
                return;
            }

            // Get some products for order items
            $products = Product::take(5)->get();
            if ($products->isEmpty()) {
                $this->command->error('❌ Tidak ada produk! Jalankan ProductSeeder terlebih dahulu.');
                return;
            }

            // --- SKENARIO 1: PENDAPATAN DITERIMA DIMUKA (DP - Akun 211) ---
            // Pelanggan bayar DP Rp 400.000 untuk pesanan total Rp 1.000.000
            $order1 = Order::create([
                'customer_id' => $customer1->id,
                'order_date' => now(),
                'total_amount' => 0, // Will be calculated from items
                'paid_amount' => 0,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => '[DUMMY-SEEDER] DP Scenario',
            ]);
            // Add order items
            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $products[0]->id ?? 1,
                'quantity' => 100,
                'unit_price' => 5000,
                'subtotal' => 500000,
            ]);
            OrderItem::create([
                'order_id' => $order1->id,
                'product_id' => $products[1]->id ?? 2,
                'quantity' => 50,
                'unit_price' => 10000,
                'subtotal' => 500000,
            ]);
            $order1->update(['total_amount' => 1000000]);
            // Jurnal: Debit Kas (111) ↔ Kredit Pendapatan Diterima Dimuka (211)
            $accountingService->recordDownPayment($order1, 400000);
            $this->command->info('📋 Skenario 1: DP Order #' . $order1->order_number . ' - Rp 400.000 (2 items)');

            // --- SKENARIO 2: PIUTANG USAHA (Akun 112) ---
            // Pesanan Rp 500.000, status SELESAI tetapi BELUM DIBAYAR
            $order2 = Order::create([
                'customer_id' => $customer2->id,
                'order_date' => now(),
                'total_amount' => 0,
                'paid_amount' => 0,
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'notes' => '[DUMMY-SEEDER] Receivable Scenario',
            ]);
            // Add order items
            OrderItem::create([
                'order_id' => $order2->id,
                'product_id' => $products[2]->id ?? 3,
                'quantity' => 200,
                'unit_price' => 2500,
                'subtotal' => 500000,
            ]);
            $order2->update(['total_amount' => 500000]);
            // Jurnal: Debit Piutang Usaha (112) ↔ Kredit Pendapatan Jasa Cetak (411)
            $accountingService->recordReceivable($order2);
            $this->command->info('📋 Skenario 2: Piutang Order #' . $order2->order_number . ' - Rp 500.000 (1 item)');

            // --- SKENARIO 3: LABA & HPP (Akun 312, 411, 511) ---
            // Pesanan Rp 300.000, LUNAS dan SELESAI. Estimasi pemakaian bahan Rp 100.000.
            $order3 = Order::create([
                'customer_id' => $customer3->id,
                'order_date' => now(),
                'total_amount' => 0,
                'paid_amount' => 300000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'notes' => '[DUMMY-SEEDER] Full Payment + HPP Scenario',
            ]);
            // Add order items
            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $products[3]->id ?? 4,
                'quantity' => 30,
                'unit_price' => 10000,
                'subtotal' => 300000,
            ]);
            $order3->update(['total_amount' => 300000]);
            // Jurnal Pendapatan: Debit Kas (111) ↔ Kredit Pendapatan Jasa Cetak (411)
            $accountingService->recordFullPayment($order3);
            // Jurnal HPP: Debit Beban HPP (511) ↔ Kredit Persediaan Bahan (113)
            $accountingService->recordHPPWithAmount($order3, 100000);
            $this->command->info('📋 Skenario 3: Full Payment Order #' . $order3->order_number . ' - Rp 300.000 (1 item, HPP: Rp 100.000)');

            // --- SKENARIO 4: HUTANG USAHA (Akun 212) ---
            // Toko membeli stok bahan baku Rp 1.500.000 secara KREDIT (Belum dibayar ke supplier)
            $accountingService->recordCreditPurchase(1500000, 'Pembelian Kertas Art Paper dari Supplier');
            $this->command->info('📋 Skenario 4: Pembelian Kredit - Rp 1.500.000');

            // --- SKENARIO 5: BEBAN OPERASIONAL (Akun 611, 612) ---
            // Mencatat pengeluaran kas harian: Gaji & Listrik
            $accountingService->recordExpense('611', 700000, 'Gaji Operator Cetak Bulan Ini');
            $accountingService->recordExpense('612', 300000, 'Tagihan Listrik Workshop');
            $this->command->info('📋 Skenario 5: Beban Operasional - Gaji Rp 700.000, Listrik Rp 300.000');
        });

        $this->command->newLine();
        $this->command->info('✅ DummyTransactionSeeder: Berhasil mensimulasikan siklus operasional lengkap.');
        $this->command->newLine();
        $this->command->info('📊 Ringkasan Transaksi:');
        $this->command->table(
            ['Akun', 'Deskripsi', 'Jumlah'],
            [
                ['111 - Kas', 'Masuk dari DP & Pembayaran', 'Rp 700.000'],
                ['111 - Kas', 'Keluar untuk Beban', '(Rp 1.000.000)'],
                ['112 - Piutang', 'Pesanan belum dibayar', 'Rp 500.000'],
                ['113 - Persediaan', 'Pembelian kredit', 'Rp 1.500.000'],
                ['113 - Persediaan', 'Dikurangi HPP', '(Rp 100.000)'],
                ['211 - DP', 'Uang muka pelanggan', 'Rp 400.000'],
                ['212 - Hutang', 'Hutang ke supplier', 'Rp 1.500.000'],
                ['411 - Pendapatan', 'Total pendapatan', 'Rp 800.000'],
                ['511 - HPP', 'Beban bahan baku', 'Rp 100.000'],
                ['611 - Gaji', 'Beban gaji', 'Rp 700.000'],
                ['612 - Listrik', 'Beban listrik', 'Rp 300.000'],
            ]
        );
    }
}
