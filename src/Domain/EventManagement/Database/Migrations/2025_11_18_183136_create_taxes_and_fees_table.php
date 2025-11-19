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
        Schema::create('taxes_and_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['tax', 'fee']);
            $table->string('name');
            $table->enum('calculation_type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 4);
            $table->enum('display_mode', ['separated', 'integrated'])->default('separated');
            $table->json('applicable_gateways')->nullable()->comment('Array of gateway names or null for all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes_and_fees');
    }
};
