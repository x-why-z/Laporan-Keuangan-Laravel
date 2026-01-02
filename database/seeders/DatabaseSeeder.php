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
            AccountSeeder::class,     // Chart of Accounts
            RoleSeeder::class,        // Roles and Users
        ]);

        // Legacy product seeding (if not already seeded)
        $this->seedProducts();
    }

    /**
     * Seed sample products for percetakan.
     */
    private function seedProducts(): void
    {
        $products = [
            ['name' => 'Spanduk', 'unit' => 'm²', 'price' => 35000, 'description' => 'Spanduk flexi full color'],
            ['name' => 'Banner', 'unit' => 'm²', 'price' => 45000, 'description' => 'Banner indoor/outdoor'],
            ['name' => 'X-Banner', 'unit' => 'pcs', 'price' => 85000, 'description' => 'X-Banner ukuran 60x160cm'],
            ['name' => 'Roll Banner', 'unit' => 'pcs', 'price' => 250000, 'description' => 'Roll Banner ukuran 85x200cm'],
            ['name' => 'Kartu Nama', 'unit' => 'box', 'price' => 50000, 'description' => 'Kartu nama art carton 260gsm, isi 100 lembar'],
            ['name' => 'Brosur A4', 'unit' => 'lembar', 'price' => 1500, 'description' => 'Brosur A4 art paper 120gsm'],
            ['name' => 'Brosur A5', 'unit' => 'lembar', 'price' => 1000, 'description' => 'Brosur A5 art paper 120gsm'],
            ['name' => 'Stiker Vinyl', 'unit' => 'm²', 'price' => 75000, 'description' => 'Stiker vinyl indoor/outdoor'],
            ['name' => 'Stiker One Way', 'unit' => 'm²', 'price' => 150000, 'description' => 'Stiker one way vision untuk kaca'],
            ['name' => 'Undangan', 'unit' => 'pcs', 'price' => 3500, 'description' => 'Undangan lipat art carton'],
            ['name' => 'Poster A3', 'unit' => 'lembar', 'price' => 15000, 'description' => 'Poster A3 art paper 260gsm'],
            ['name' => 'Poster A2', 'unit' => 'lembar', 'price' => 25000, 'description' => 'Poster A2 art paper 260gsm'],
            ['name' => 'Nota/Kwitansi', 'unit' => 'rim', 'price' => 150000, 'description' => 'Nota rangkap 2-3 ply NCR'],
            ['name' => 'ID Card', 'unit' => 'pcs', 'price' => 25000, 'description' => 'ID Card PVC full color'],
            ['name' => 'Sertifikat', 'unit' => 'lembar', 'price' => 5000, 'description' => 'Sertifikat art carton 260gsm'],
        ];

        foreach ($products as $product) {
            \App\Models\Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}

