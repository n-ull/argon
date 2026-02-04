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
        Schema::dropIfExists('combo_items');
        Schema::dropIfExists('combos');

        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('combo_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combo_id')->constrained('combos')->cascadeOnDelete();
            $table->foreignId('product_price_id')->constrained('product_prices')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('combo_id')->nullable()->constrained('combos')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->unsignedBigInteger('product_price_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['combo_id']);
            $table->dropColumn('combo_id');
            // We can't easily revert the nullable change without risking data integrity issues if nulls were introduced
            // For now, we'll leave them as nullable in down or attempt to revert if empty
            // $table->unsignedBigInteger('product_id')->nullable(false)->change(); 
        });

        Schema::dropIfExists('combo_items');
        Schema::dropIfExists('combos');
    }
};
