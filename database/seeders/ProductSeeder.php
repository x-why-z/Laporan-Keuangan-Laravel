<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed products based on Percetakan Mutiara Rizki catalog.
     * 
     * Products are categorized into two pricing types:
     * - unit: Fixed price per unit (buku, rim, paket)
     * - area: Price based on dimensions (P x L in m²)
     */
    public function run(): void
    {
        $products = [
            // ===== PRODUK BERBASIS UNIT (Fixed Price) =====
            // Buku & Publikasi
            ['name' => 'Buku', 'unit' => 'eksemplar', 'price' => 25000, 'price_type' => 'unit', 'description' => 'Cetak buku (harga dasar, tergantung jumlah halaman)'],
            ['name' => 'Agenda', 'unit' => 'pcs', 'price' => 45000, 'price_type' => 'unit', 'description' => 'Agenda tahunan custom'],
            ['name' => 'Jurnal', 'unit' => 'eksemplar', 'price' => 35000, 'price_type' => 'unit', 'description' => 'Jurnal ilmiah / akademik'],
            ['name' => 'Majalah', 'unit' => 'eksemplar', 'price' => 20000, 'price_type' => 'unit', 'description' => 'Majalah full color'],
            ['name' => 'Buklet', 'unit' => 'pcs', 'price' => 5000, 'price_type' => 'unit', 'description' => 'Buklet lipat (8-16 halaman)'],
            
            // Undangan & Kartu
            ['name' => 'Undangan', 'unit' => 'pcs', 'price' => 3500, 'price_type' => 'unit', 'description' => 'Undangan lipat art carton'],
            ['name' => 'Amplop', 'unit' => 'lembar', 'price' => 500, 'price_type' => 'unit', 'description' => 'Amplop custom dengan logo'],
            ['name' => 'Kop Surat', 'unit' => 'rim', 'price' => 75000, 'price_type' => 'unit', 'description' => 'Kop surat HVS 80gsm (500 lembar)'],
            ['name' => 'Kartu Nama', 'unit' => 'box', 'price' => 50000, 'price_type' => 'unit', 'description' => 'Kartu nama art carton 260gsm, isi 100 lembar'],
            
            // Formulir & Dokumen
            ['name' => 'Formulir', 'unit' => 'rim', 'price' => 100000, 'price_type' => 'unit', 'description' => 'Formulir HVS custom'],
            ['name' => 'Nota NCR', 'unit' => 'rim', 'price' => 150000, 'price_type' => 'unit', 'description' => 'Nota rangkap 2-3 ply NCR'],
            
            // Kemasan
            ['name' => 'Kemasan', 'unit' => 'pcs', 'price' => 5000, 'price_type' => 'unit', 'description' => 'Box kemasan custom (die cut)'],
            ['name' => 'Kantong Belanja', 'unit' => 'pcs', 'price' => 3000, 'price_type' => 'unit', 'description' => 'Paper bag custom'],
            
            // ===== PRODUK BERBASIS AREA (P x L) =====
            // Banner & Poster
            ['name' => 'Poster', 'unit' => 'm²', 'price' => 75000, 'price_type' => 'area', 'description' => 'Poster indoor full color'],
            ['name' => 'Banner', 'unit' => 'm²', 'price' => 45000, 'price_type' => 'area', 'description' => 'Banner indoor/outdoor'],
            ['name' => 'Spanduk', 'unit' => 'm²', 'price' => 35000, 'price_type' => 'area', 'description' => 'Spanduk flexi full color'],
            
            // Sticker & Label
            ['name' => 'Sticker', 'unit' => 'm²', 'price' => 75000, 'price_type' => 'area', 'description' => 'Sticker vinyl indoor/outdoor'],
            ['name' => 'Label', 'unit' => 'm²', 'price' => 85000, 'price_type' => 'area', 'description' => 'Label produk vinyl'],
            
            // Kalender
            ['name' => 'Kalender Poster', 'unit' => 'm²', 'price' => 80000, 'price_type' => 'area', 'description' => 'Kalender poster besar'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }

        $this->command->info('✅ ProductSeeder: ' . count($products) . ' products seeded successfully.');
    }
}
