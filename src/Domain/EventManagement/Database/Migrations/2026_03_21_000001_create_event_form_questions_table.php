<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->enum('applies_to', ['order', 'product'])->default('order');
            $table->boolean('is_active')->default(false);
            $table->json('fields'); // array of { id, label, type, required, options[] }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_form_questions');
    }
};
