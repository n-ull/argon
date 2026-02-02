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
        // 1. Update promoters table
        Schema::table('promoters', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->string('commission_type')->default('fixed')->after('referral_code');
            $table->decimal('commission_value', 10, 2)->default(0)->after('commission_type');
        });

        // Migrate existing promoters
        $promoters = DB::table('promoters')->get();
        foreach ($promoters as $promoter) {
            // Find first event linked to this promoter
            // We need to use raw SQL if models are not reliable here, but DB facade is fine.
            // join promoter_events to get event_id, then join events to get organizer_id
            $data = DB::table('promoter_events')
                ->join('events', 'promoter_events.event_id', '=', 'events.id')
                ->where('promoter_events.promoter_id', $promoter->id)
                ->select('events.organizer_id', 'promoter_events.commission_type', 'promoter_events.commission_value')
                ->first();

            if ($data) {
                DB::table('promoters')
                    ->where('id', $promoter->id)
                    ->update([
                        'organizer_id' => $data->organizer_id,
                        'commission_type' => $data->commission_type ?? 'fixed',
                        'commission_value' => $data->commission_value ?? 0,
                    ]);
            }
        }

        // 2. Update promoter_invitations table
        Schema::table('promoter_invitations', function (Blueprint $table) {
            $table->foreignId('organizer_id')->nullable()->after('promoter_id')->constrained()->onDelete('cascade');
        });

        // Migrate invitations
        $invitations = DB::table('promoter_invitations')->get();
        foreach ($invitations as $invitation) {
            $event = DB::table('events')->where('id', $invitation->event_id)->first();
            if ($event) {
                DB::table('promoter_invitations')
                    ->where('id', $invitation->id)
                    ->update(['organizer_id' => $event->organizer_id]);
            }
        }

        Schema::table('promoter_invitations', function (Blueprint $table) {
            // Drop foreign key if it exists (assuming naming convention)
            // Or just drop column which usually requires dropping FK first
            // Checking standard Laravel naming: promoter_invitations_event_id_foreign
            $table->dropConstrainedForeignId('event_id');
        });

        // 3. Drop promoter_events table
        Schema::dropIfExists('promoter_events');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive migration, hard to reverse perfectly.

        // Re-create promoter_events
        Schema::create('promoter_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('commission_type')->default('fixed');
            $table->decimal('commission_value', 10, 2)->default(0);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Restore promoter_invitations
        Schema::table('promoter_invitations', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->dropColumn('organizer_id');
        });

        // Restore promoters
        Schema::table('promoters', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->dropColumn(['organizer_id', 'commission_type', 'commission_value']);
        });
    }
};
