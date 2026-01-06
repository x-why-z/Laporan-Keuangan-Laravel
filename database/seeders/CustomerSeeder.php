<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Budi Sudarsono',
                'phone' => '08123456789',
                'address' => 'Jl. Merdeka No. 123, Jakarta Selatan',
            ],
            [
                'name' => 'Siska Amelia',
                'phone' => '08771234567',
                'address' => 'Jl. Gatot Subroto No. 45, Bandung',
            ],
            [
                'name' => 'PT Maju Jaya',
                'phone' => '0219876543',
                'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
            ],
            [
                'name' => 'CV Berkah Abadi',
                'phone' => '08159876543',
                'address' => 'Jl. Sudirman No. 88, Surabaya',
            ],
            [
                'name' => 'Toko Gemilang',
                'phone' => '08521234567',
                'address' => 'Jl. Ahmad Yani No. 56, Semarang',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['name' => $customer['name']],
                $customer
            );
        }

        $this->command->info('✅ CustomerSeeder: 5 pelanggan dummy berhasil ditambahkan.');
    }
}
