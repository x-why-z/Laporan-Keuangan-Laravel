<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('material')->nullable()->after('specifications');
            $table->string('finishing')->nullable()->after('material');
            $table->string('binding_type')->nullable()->after('finishing');
            $table->decimal('finishing_cost', 15, 2)->default(0)->after('binding_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['material', 'finishing', 'binding_type', 'finishing_cost']);
        });
    }
};
