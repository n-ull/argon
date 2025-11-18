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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_before_additions', 10, 2)->default(0)->comment('Total price without any additional cost');
            $table->decimal('total_gross', 10, 2)->default(0)->comment('Total price with all the additions');
            $table->enum('status', ['pending', 'expired', 'cancelled', 'refunded', 'paid'])->default('pending');
            $table->string('reference_id')->unique()->comment('Reference used to find the order through the payment gateway.');
            $table->enum('organizer_raise_method_snapshot', ['internal', 'split'])->nullable()->comment('The preferred raise money method used by the organization at the moment the order was paid.');
            $table->string('used_payment_gateway_snapshot')->nullable()->comment('The payment gateway used at the moment the order was paid.');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
