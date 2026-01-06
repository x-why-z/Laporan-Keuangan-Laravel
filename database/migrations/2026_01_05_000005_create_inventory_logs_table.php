<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel inventory_logs: Mencatat history pergerakan stok (masuk/keluar).
     */
    public function up(): void
    {
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);              // in=masuk, out=keluar
            $table->decimal('quantity', 15, 2);               // Jumlah pergerakan
            $table->decimal('cost_per_unit', 15, 2)->nullable(); // Harga per unit (untuk pembelian)
            $table->string('reference_type')->nullable();     // order, purchase, adjustment
            $table->unsignedBigInteger('reference_id')->nullable(); // ID referensi
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            
            $table->index(['material_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
