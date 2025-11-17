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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedInteger('max_per_order')->nullable();
            $table->unsignedInteger('min_per_order')->nullable();
            $table->string('product_type')->default('ticket');
            $table->string('product_price_type')->default('standard');
            $table->boolean('hide_before_sale_start_date')->default(false);
            $table->boolean('hide_after_sale_end_date')->default(false);
            $table->boolean('hide_when_sold_out')->default(false);
            $table->boolean('show_stock')->default(false);
            $table->dateTime('start_sale_date')->nullable();
            $table->dateTime('end_sale_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
