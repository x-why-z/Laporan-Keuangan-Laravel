<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel materials: Menyimpan data bahan baku untuk perhitungan HPP.
     */
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // Nama bahan: Kertas A3, Tinta, Bahan Spanduk
            $table->string('unit');                           // Satuan: lembar, liter, meter, kg
            $table->decimal('cost_per_unit', 15, 2);          // Harga beli per satuan
            $table->decimal('stock_quantity', 15, 2)->default(0);  // Jumlah stok saat ini
            $table->decimal('min_stock', 15, 2)->default(0);  // Minimum stok (untuk peringatan)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
