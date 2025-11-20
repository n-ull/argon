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
            $table->decimal('subtotal', 10, 2)->default(0)->after('used_payment_gateway_snapshot');
            $table->decimal('taxes_total', 10, 2)->default(0)->after('subtotal');
            $table->decimal('fees_total', 10, 2)->default(0)->after('taxes_total');
            $table->json('items_snapshot')->nullable()->after('fees_total')->comment('Snapshot of order items with prices at purchase time');
            $table->json('taxes_snapshot')->nullable()->after('items_snapshot')->comment('Applied taxes snapshot');
            $table->json('fees_snapshot')->nullable()->after('taxes_snapshot')->comment('Applied fees snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'taxes_total', 'fees_total', 'items_snapshot', 'taxes_snapshot', 'fees_snapshot']);
        });
    }
};
