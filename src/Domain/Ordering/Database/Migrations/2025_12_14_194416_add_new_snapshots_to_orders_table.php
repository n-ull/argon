<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_gross', 10, 2)->default(0)->comment('Total gross amount')->after('subtotal');
            $table->decimal('total_before_additions', 10, 2)->default(0)->comment('Total before additions')->after('total_gross');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_gross');
            $table->dropColumn('total_before_additions');
        });
    }
};
