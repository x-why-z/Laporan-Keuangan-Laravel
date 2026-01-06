<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Material;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpeningBalanceSeeder extends Seeder
{
    /**
     * Seed opening balance for professional Balance Sheet.
     * 
     * Persamaan Akuntansi:
     * Aset (Rp 75.000.000) = Kewajiban (Rp 0) + Modal (Rp 75.000.000)
     * 
     * Jurnal Saldo Awal:
     * - Debit Kas (111): Variable (Total Modal - Nilai Persediaan)
     * - Debit Persediaan Bahan (113): Sesuai nilai material di database
     * - Kredit Modal Pemilik (311): Rp 75.000.000
     */
    public function run(): void
    {
        // Total modal pemilik yang diinginkan
        $totalModal = 75000000;

        // Hitung total nilai persediaan dari tabel materials
        $totalInventory = Material::where('is_active', true)
            ->selectRaw('SUM(cost_per_unit * stock_quantity) as total_value')
            ->value('total_value') ?? 0;

        $totalInventory = (float) $totalInventory;

        // Hitung nilai kas = Total Modal - Persediaan
        $cashAmount = $totalModal - $totalInventory;

        if ($cashAmount < 0) {
            $this->command->error("⚠️ Error: Nilai persediaan (Rp " . number_format($totalInventory, 0, ',', '.') . ") melebihi total modal (Rp " . number_format($totalModal, 0, ',', '.') . ")");
            return;
        }

        DB::transaction(function () use ($cashAmount, $totalInventory, $totalModal) {
            $referenceNumber = 'OPEN-' . now()->format('Ymd') . '-001';
            $description = 'Saldo Awal - Modal Pemilik';
            $transactionDate = now()->toDateString();

            // 1. Debit Kas (111) - Menambah aset
            $kasAccount = Account::findByCode('111');
            if ($kasAccount && $cashAmount > 0) {
                Transaction::create([
                    'reference_number' => $referenceNumber . '-KAS',
                    'account_id' => $kasAccount->id,
                    'type' => 'debit',
                    'amount' => $cashAmount,
                    'description' => $description . ' - Kas',
                    'transaction_date' => $transactionDate,
                ]);
                $kasAccount->updateBalance('debit', $cashAmount);
                $this->command->info("✅ Kas (111): Debit Rp " . number_format($cashAmount, 0, ',', '.'));
            }

            // 2. Debit Persediaan Bahan (113) - Menambah aset
            $persediaanAccount = Account::findByCode('113');
            if ($persediaanAccount && $totalInventory > 0) {
                Transaction::create([
                    'reference_number' => $referenceNumber . '-INV',
                    'account_id' => $persediaanAccount->id,
                    'type' => 'debit',
                    'amount' => $totalInventory,
                    'description' => $description . ' - Persediaan Bahan Baku',
                    'transaction_date' => $transactionDate,
                ]);
                $persediaanAccount->updateBalance('debit', $totalInventory);
                $this->command->info("✅ Persediaan Bahan (113): Debit Rp " . number_format($totalInventory, 0, ',', '.'));
            }

            // 3. Kredit Modal Pemilik (311) - Menambah ekuitas
            $modalAccount = Account::findByCode('311');
            if ($modalAccount) {
                Transaction::create([
                    'reference_number' => $referenceNumber . '-MOD',
                    'account_id' => $modalAccount->id,
                    'type' => 'credit',
                    'amount' => $totalModal,
                    'description' => $description,
                    'transaction_date' => $transactionDate,
                ]);
                $modalAccount->updateBalance('credit', $totalModal);
                $this->command->info("✅ Modal Pemilik (311): Kredit Rp " . number_format($totalModal, 0, ',', '.'));
            }

            $this->command->newLine();
            $this->command->info("═══════════════════════════════════════════════════════");
            $this->command->info("📊 SALDO AWAL BERHASIL DIINISIALISASI");
            $this->command->info("═══════════════════════════════════════════════════════");
            $this->command->info("   Kas             : Rp " . number_format($cashAmount, 0, ',', '.'));
            $this->command->info("   Persediaan Bahan: Rp " . number_format($totalInventory, 0, ',', '.'));
            $this->command->info("   ─────────────────────────────────────────────────────");
            $this->command->info("   TOTAL ASET      : Rp " . number_format($totalModal, 0, ',', '.'));
            $this->command->info("   TOTAL MODAL     : Rp " . number_format($totalModal, 0, ',', '.'));
            $this->command->info("═══════════════════════════════════════════════════════");
            $this->command->info("✅ Neraca Seimbang: Aset = Kewajiban + Modal");
            $this->command->newLine();
        });
    }
}
