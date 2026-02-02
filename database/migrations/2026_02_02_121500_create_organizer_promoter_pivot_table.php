<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create organizer_promoter pivot table
        Schema::create('organizer_promoter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promoter_id')->constrained('promoters')->cascadeOnDelete();
            $table->string('commission_type')->default('fixed');
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['organizer_id', 'promoter_id']);
        });

        // 2. Migrate existing data from promoters table to pivot
        // Check if columns exist first to avoid errors if run in fresh state without previous refactor
        if (Schema::hasColumn('promoters', 'organizer_id')) {
            $promoters = DB::table('promoters')->whereNotNull('organizer_id')->get();
            foreach ($promoters as $promoter) {
                DB::table('organizer_promoter')->insert([
                    'promoter_id' => $promoter->id,
                    'organizer_id' => $promoter->organizer_id,
                    'commission_type' => $promoter->commission_type ?? 'fixed',
                    'commission_value' => $promoter->commission_value ?? 0,
                    'enabled' => $promoter->enabled ?? true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Drop columns from promoters table
            Schema::table('promoters', function (Blueprint $table) {
                // Drop FK first
                // Need to guess the array syntax or name. Standard is table_column_foreign
                $table->dropForeign(['organizer_id']);
                $table->dropColumn(['organizer_id', 'commission_type', 'commission_value']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to promoters table
        Schema::table('promoters', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('commission_type')->default('fixed');
            $table->decimal('commission_value', 10, 2)->default(0);
        });

        // Migrate data back (approximate, since N:M -> 1:1 loses data)
        $pivots = DB::table('organizer_promoter')->get();
        foreach ($pivots as $pivot) {
            // This will overwrite if a promoter has multiple organizers, taking the last one.
            DB::table('promoters')
                ->where('id', $pivot->promoter_id)
                ->update([
                    'organizer_id' => $pivot->organizer_id,
                    'commission_type' => $pivot->commission_type,
                    'commission_value' => $pivot->commission_value,
                ]);
        }

        Schema::dropIfExists('organizer_promoter');
    }
};
