<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_tax_and_fee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_and_fee_id')->constrained('taxes_and_fees')->cascadeOnDelete();
            $table->integer('sort_order')->default(0)->comment('Order of application for this event');
            $table->timestamps();

            $table->unique(['event_id', 'tax_and_fee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tax_and_fee');
    }
};
