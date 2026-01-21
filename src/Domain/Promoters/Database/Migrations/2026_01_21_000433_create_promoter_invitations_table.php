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
        Schema::create('promoter_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token');
            $table->enum('status', ['pending', 'accepted', 'expired', 'declined']);
            $table->foreignId('promoter_id')->constrained('promoters')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoter_invitations');
    }
};
