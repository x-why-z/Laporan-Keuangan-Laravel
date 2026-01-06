<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Chart of Accounts (COA) standar UMKM Percetakan
     * Diimpor dari file CSV untuk kemudahan maintenance
     */
    public function run(): void
    {
        $csvFile = database_path('data/accounts.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        $data = array_map('str_getcsv', file($csvFile));
        $header = array_shift($data);

        $count = 0;
        foreach ($data as $row) {
            // Skip empty rows
            if (empty($row[0])) {
                continue;
            }

            $accountData = array_combine($header, $row);

            Account::updateOrCreate(
                ['code' => $accountData['code']],
                [
                    'name' => $accountData['name'],
                    'type' => $accountData['type'],
                    'category' => $accountData['category'],
                    'description' => $accountData['description'],
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->command->info("✅ AccountSeeder: Berhasil mengimpor {$count} akun dari CSV.");
    }
}
