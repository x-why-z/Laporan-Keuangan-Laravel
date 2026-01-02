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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('down_payment', 15, 2)->default(0)->after('total_amount');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('down_payment');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('paid_amount');
            $table->enum('production_status', ['queue', 'process', 'done', 'picked_up'])->default('queue')->after('status');
            $table->timestamp('voided_at')->nullable()->after('notes');
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete()->after('voided_at');
            $table->string('void_reason')->nullable()->after('voided_by');
            
            $table->index('payment_status');
            $table->index('production_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['production_status']);
            $table->dropForeign(['voided_by']);
            $table->dropColumn([
                'down_payment',
                'paid_amount',
                'payment_status',
                'production_status',
                'voided_at',
                'voided_by',
                'void_reason',
            ]);
        });
    }
};
