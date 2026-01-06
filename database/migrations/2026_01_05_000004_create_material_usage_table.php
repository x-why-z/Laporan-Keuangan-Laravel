<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel material_usage: Mencatat pemakaian bahan per item pesanan untuk HPP.
     */
    public function up(): void
    {
        Schema::create('material_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_used', 15, 2);          // Jumlah bahan yang dipakai
            $table->decimal('cost_per_unit', 15, 2);          // Snapshot harga saat digunakan
            $table->decimal('total_cost', 15, 2);             // HPP untuk material ini (qty * cost)
            $table->timestamps();
            
            $table->index(['order_item_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_usage');
    }
};
