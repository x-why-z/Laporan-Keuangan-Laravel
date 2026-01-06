<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Seed materials (bahan baku) for Percetakan Mutiara Rizki.
     * 
     * Categories:
     * - Kertas (Paper): HVS, Art Paper, Art Carton, NCR, dll.
     * - Tinta (Ink): CMYK, Sublimation, UV
     * - Bahan Banner/Spanduk: Flexi, Vinyl, Albatros
     * - Finishing: Laminasi, Foil, Lem, Binding
     */
    public function run(): void
    {
        $materials = [
            // ===== KERTAS (PAPER) =====
            [
                'name' => 'HVS 70gsm',
                'unit' => 'rim',
                'cost_per_unit' => 45000,
                'stock_quantity' => 100,
                'min_stock' => 20,
                'description' => 'Kertas HVS 70gsm ukuran A4 (500 lembar/rim)',
                'is_active' => true
            ],
            [
                'name' => 'HVS 80gsm',
                'unit' => 'rim',
                'cost_per_unit' => 52000,
                'stock_quantity' => 80,
                'min_stock' => 15,
                'description' => 'Kertas HVS 80gsm ukuran A4 (500 lembar/rim)',
                'is_active' => true
            ],
            [
                'name' => 'Art Paper 100gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 800,
                'stock_quantity' => 500,
                'min_stock' => 100,
                'description' => 'Art Paper glossy 100gsm untuk brosur/majalah',
                'is_active' => true
            ],
            [
                'name' => 'Art Paper 120gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 1000,
                'stock_quantity' => 400,
                'min_stock' => 80,
                'description' => 'Art Paper glossy 120gsm untuk majalah/buklet',
                'is_active' => true
            ],
            [
                'name' => 'Art Paper 150gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 1200,
                'stock_quantity' => 300,
                'min_stock' => 60,
                'description' => 'Art Paper glossy 150gsm untuk cover/flyer premium',
                'is_active' => true
            ],
            [
                'name' => 'Art Carton 210gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 1500,
                'stock_quantity' => 250,
                'min_stock' => 50,
                'description' => 'Art Carton 210gsm untuk undangan/kartu',
                'is_active' => true
            ],
            [
                'name' => 'Art Carton 260gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 2000,
                'stock_quantity' => 200,
                'min_stock' => 40,
                'description' => 'Art Carton 260gsm untuk kartu nama/undangan premium',
                'is_active' => true
            ],
            [
                'name' => 'Art Carton 310gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 2500,
                'stock_quantity' => 150,
                'min_stock' => 30,
                'description' => 'Art Carton 310gsm untuk cover buku/box',
                'is_active' => true
            ],
            [
                'name' => 'Kertas NCR 2 Ply',
                'unit' => 'rim',
                'cost_per_unit' => 180000,
                'stock_quantity' => 20,
                'min_stock' => 5,
                'description' => 'Kertas NCR 2 rangkap (putih-kuning) untuk nota',
                'is_active' => true
            ],
            [
                'name' => 'Kertas NCR 3 Ply',
                'unit' => 'rim',
                'cost_per_unit' => 250000,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'description' => 'Kertas NCR 3 rangkap (putih-merah-kuning) untuk faktur',
                'is_active' => true
            ],
            [
                'name' => 'Kertas Doorslag',
                'unit' => 'rim',
                'cost_per_unit' => 35000,
                'stock_quantity' => 30,
                'min_stock' => 10,
                'description' => 'Kertas doorslag tipis untuk tembusan',
                'is_active' => true
            ],
            [
                'name' => 'Duplex 250gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 1800,
                'stock_quantity' => 200,
                'min_stock' => 40,
                'description' => 'Duplex board untuk kemasan/box',
                'is_active' => true
            ],
            [
                'name' => 'Ivory 300gsm',
                'unit' => 'lembar',
                'cost_per_unit' => 3000,
                'stock_quantity' => 100,
                'min_stock' => 20,
                'description' => 'Ivory board premium untuk kemasan/undangan',
                'is_active' => true
            ],

            // ===== TINTA (INK) =====
            [
                'name' => 'Tinta Cyan',
                'unit' => 'liter',
                'cost_per_unit' => 350000,
                'stock_quantity' => 5,
                'min_stock' => 2,
                'description' => 'Tinta CMYK - Cyan untuk mesin offset/digital',
                'is_active' => true
            ],
            [
                'name' => 'Tinta Magenta',
                'unit' => 'liter',
                'cost_per_unit' => 350000,
                'stock_quantity' => 5,
                'min_stock' => 2,
                'description' => 'Tinta CMYK - Magenta untuk mesin offset/digital',
                'is_active' => true
            ],
            [
                'name' => 'Tinta Yellow',
                'unit' => 'liter',
                'cost_per_unit' => 350000,
                'stock_quantity' => 5,
                'min_stock' => 2,
                'description' => 'Tinta CMYK - Yellow untuk mesin offset/digital',
                'is_active' => true
            ],
            [
                'name' => 'Tinta Black',
                'unit' => 'liter',
                'cost_per_unit' => 300000,
                'stock_quantity' => 8,
                'min_stock' => 3,
                'description' => 'Tinta CMYK - Black/Key untuk mesin offset/digital',
                'is_active' => true
            ],
            [
                'name' => 'Tinta Eco-Solvent',
                'unit' => 'liter',
                'cost_per_unit' => 250000,
                'stock_quantity' => 10,
                'min_stock' => 3,
                'description' => 'Tinta eco-solvent untuk banner/sticker outdoor',
                'is_active' => true
            ],

            // ===== BAHAN BANNER & SPANDUK =====
            [
                'name' => 'Flexi Korea 280gsm',
                'unit' => 'meter',
                'cost_per_unit' => 8000,
                'stock_quantity' => 200,
                'min_stock' => 50,
                'description' => 'Bahan flexi Korea 280gsm untuk spanduk indoor',
                'is_active' => true
            ],
            [
                'name' => 'Flexi China 340gsm',
                'unit' => 'meter',
                'cost_per_unit' => 6000,
                'stock_quantity' => 300,
                'min_stock' => 100,
                'description' => 'Bahan flexi China 340gsm untuk spanduk outdoor',
                'is_active' => true
            ],
            [
                'name' => 'Vinyl Sticker Glossy',
                'unit' => 'meter',
                'cost_per_unit' => 12000,
                'stock_quantity' => 150,
                'min_stock' => 30,
                'description' => 'Vinyl sticker glossy untuk cutting/print',
                'is_active' => true
            ],
            [
                'name' => 'Vinyl Sticker Matte',
                'unit' => 'meter',
                'cost_per_unit' => 13000,
                'stock_quantity' => 100,
                'min_stock' => 25,
                'description' => 'Vinyl sticker matte untuk label premium',
                'is_active' => true
            ],
            [
                'name' => 'Vinyl Transparan',
                'unit' => 'meter',
                'cost_per_unit' => 15000,
                'stock_quantity' => 50,
                'min_stock' => 15,
                'description' => 'Vinyl sticker transparan untuk kaca/window',
                'is_active' => true
            ],
            [
                'name' => 'Albatros',
                'unit' => 'meter',
                'cost_per_unit' => 18000,
                'stock_quantity' => 80,
                'min_stock' => 20,
                'description' => 'Kain albatros untuk backdrop/umbul-umbul',
                'is_active' => true
            ],
            [
                'name' => 'Luster/Photo Paper',
                'unit' => 'meter',
                'cost_per_unit' => 25000,
                'stock_quantity' => 50,
                'min_stock' => 10,
                'description' => 'Photo paper luster untuk poster indoor premium',
                'is_active' => true
            ],

            // ===== FINISHING MATERIALS =====
            [
                'name' => 'Laminasi Glossy',
                'unit' => 'meter',
                'cost_per_unit' => 3500,
                'stock_quantity' => 200,
                'min_stock' => 50,
                'description' => 'Roll laminasi glossy untuk finishing kilap',
                'is_active' => true
            ],
            [
                'name' => 'Laminasi Doff',
                'unit' => 'meter',
                'cost_per_unit' => 4000,
                'stock_quantity' => 150,
                'min_stock' => 40,
                'description' => 'Roll laminasi doff/matte untuk finishing elegan',
                'is_active' => true
            ],
            [
                'name' => 'Lem Jilid Panas',
                'unit' => 'kg',
                'cost_per_unit' => 75000,
                'stock_quantity' => 10,
                'min_stock' => 3,
                'description' => 'Lem hotmelt untuk perfect binding',
                'is_active' => true
            ],
            [
                'name' => 'Lem PVA',
                'unit' => 'kg',
                'cost_per_unit' => 35000,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'description' => 'Lem putih PVA untuk laminasi/tempel',
                'is_active' => true
            ],
            [
                'name' => 'Kawat Jilid',
                'unit' => 'box',
                'cost_per_unit' => 45000,
                'stock_quantity' => 10,
                'min_stock' => 3,
                'description' => 'Kawat jilid spiral/steples untuk binding',
                'is_active' => true
            ],
            [
                'name' => 'Ring Jilid Plastik 10mm',
                'unit' => 'box',
                'cost_per_unit' => 55000,
                'stock_quantity' => 8,
                'min_stock' => 2,
                'description' => 'Ring jilid plastik 10mm (100pcs/box)',
                'is_active' => true
            ],
            [
                'name' => 'Ring Jilid Plastik 14mm',
                'unit' => 'box',
                'cost_per_unit' => 65000,
                'stock_quantity' => 8,
                'min_stock' => 2,
                'description' => 'Ring jilid plastik 14mm (100pcs/box)',
                'is_active' => true
            ],
            [
                'name' => 'Foil Emas',
                'unit' => 'meter',
                'cost_per_unit' => 50000,
                'stock_quantity' => 20,
                'min_stock' => 5,
                'description' => 'Foil emas untuk hot stamping',
                'is_active' => true
            ],
            [
                'name' => 'Foil Perak',
                'unit' => 'meter',
                'cost_per_unit' => 50000,
                'stock_quantity' => 15,
                'min_stock' => 5,
                'description' => 'Foil perak untuk hot stamping',
                'is_active' => true
            ],

            // ===== BAHAN KEMASAN =====
            [
                'name' => 'Kraft Paper',
                'unit' => 'lembar',
                'cost_per_unit' => 1200,
                'stock_quantity' => 200,
                'min_stock' => 50,
                'description' => 'Kertas kraft coklat untuk paper bag/kemasan eco',
                'is_active' => true
            ],
            [
                'name' => 'Tali Rami',
                'unit' => 'gulung',
                'cost_per_unit' => 25000,
                'stock_quantity' => 20,
                'min_stock' => 5,
                'description' => 'Tali rami untuk handle paper bag',
                'is_active' => true
            ],
            [
                'name' => 'Pita Satin',
                'unit' => 'gulung',
                'cost_per_unit' => 15000,
                'stock_quantity' => 30,
                'min_stock' => 10,
                'description' => 'Pita satin untuk finishing undangan/kemasan',
                'is_active' => true
            ],
        ];

        foreach ($materials as $material) {
            Material::updateOrCreate(
                ['name' => $material['name']],
                $material
            );
        }

        $this->command->info('✅ MaterialSeeder: ' . count($materials) . ' materials seeded successfully.');
    }
}
