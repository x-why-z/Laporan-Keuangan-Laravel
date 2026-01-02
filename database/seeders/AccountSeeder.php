<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Chart of Accounts (COA) standar UMKM Percetakan
     */
    public function run(): void
    {
        $accounts = [
            // ASET (Asset)
            ['code' => '111', 'name' => 'Kas', 'type' => 'asset', 'description' => 'Kas tunai perusahaan'],
            ['code' => '112', 'name' => 'Piutang Usaha', 'type' => 'asset', 'description' => 'Piutang dari pelanggan'],
            ['code' => '113', 'name' => 'Persediaan Bahan', 'type' => 'asset', 'description' => 'Persediaan bahan baku cetak'],
            ['code' => '121', 'name' => 'Peralatan', 'type' => 'asset', 'description' => 'Peralatan dan mesin cetak'],
            ['code' => '122', 'name' => 'Akumulasi Penyusutan Peralatan', 'type' => 'asset', 'description' => 'Akumulasi depresiasi peralatan'],
            
            // KEWAJIBAN (Liability)
            ['code' => '211', 'name' => 'Pendapatan Diterima Dimuka', 'type' => 'liability', 'description' => 'DP/Uang muka dari pelanggan'],
            ['code' => '212', 'name' => 'Hutang Usaha', 'type' => 'liability', 'description' => 'Hutang kepada supplier'],
            
            // MODAL (Equity)
            ['code' => '311', 'name' => 'Modal Pemilik', 'type' => 'equity', 'description' => 'Modal dari pemilik usaha'],
            ['code' => '312', 'name' => 'Laba Ditahan', 'type' => 'equity', 'description' => 'Laba yang tidak dibagikan'],
            
            // PENDAPATAN (Revenue)
            ['code' => '411', 'name' => 'Pendapatan Jasa Cetak', 'type' => 'revenue', 'description' => 'Pendapatan dari jasa percetakan'],
            ['code' => '412', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'description' => 'Pendapatan non-operasional'],
            
            // BEBAN (Expense)
            ['code' => '511', 'name' => 'Beban Bahan Baku', 'type' => 'expense', 'description' => 'Biaya bahan baku cetak'],
            ['code' => '512', 'name' => 'Beban Gaji', 'type' => 'expense', 'description' => 'Biaya gaji karyawan'],
            ['code' => '513', 'name' => 'Beban Listrik', 'type' => 'expense', 'description' => 'Biaya listrik operasional'],
            ['code' => '514', 'name' => 'Beban Sewa', 'type' => 'expense', 'description' => 'Biaya sewa tempat usaha'],
            ['code' => '515', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'description' => 'Biaya depresiasi peralatan'],
            ['code' => '516', 'name' => 'Beban Operasional Lainnya', 'type' => 'expense', 'description' => 'Beban operasional lain-lain'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
