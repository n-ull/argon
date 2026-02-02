<?php

namespace Tests\Feature\Promoters\Commands;

use Tests\TestCase;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Models\PromoterInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoterInvitationMail;

class BulkInvitePromotersTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_invite_command_clean_list()
    {
        Mail::fake();
        $organizer = Organizer::factory()->create();
        $file = sys_get_temp_dir().'/clean_emails.txt';
        file_put_contents($file, "test1@example.com\ntest2@example.com");

        $this->artisan('promoters:bulk-invite', [
            'organizer' => $organizer->id,
            'file' => $file,
        ])->assertSuccessful();

        Mail::assertQueued(PromoterInvitationMail::class, 2);
        $this->assertDatabaseHas('promoter_invitations', ['email' => 'test1@example.com', 'organizer_id' => $organizer->id]);
        $this->assertDatabaseHas('promoter_invitations', ['email' => 'test2@example.com', 'organizer_id' => $organizer->id]);

        unlink($file);
    }

    public function test_bulk_invite_command_messy_file()
    {
        Mail::fake();
        $organizer = Organizer::factory()->create();
        $file = sys_get_temp_dir().'/messy_emails.txt';
        $content = "Here is an email: test1@example.com in a sentence.\nAnd another one <test2@example.com>.";
        file_put_contents($file, $content);

        $this->artisan('promoters:bulk-invite', [
            'organizer' => $organizer->id,
            'file' => $file,
        ])->assertSuccessful();

        Mail::assertQueued(PromoterInvitationMail::class, 2);

        $this->assertDatabaseHas('promoter_invitations', ['email' => 'test1@example.com']);
        $this->assertDatabaseHas('promoter_invitations', ['email' => 'test2@example.com']);

        unlink($file);
    }

    public function test_bulk_invite_command_duplicates()
    {
        Mail::fake();
        $organizer = Organizer::factory()->create();
        $file = sys_get_temp_dir().'/duplicate_emails.txt';
        file_put_contents($file, "test1@example.com\ntest1@example.com\nAnother: test1@example.com");

        $this->artisan('promoters:bulk-invite', [
            'organizer' => $organizer->id,
            'file' => $file,
        ])->assertSuccessful();

        // Should only send 1 email
        Mail::assertQueued(PromoterInvitationMail::class, 1);

        unlink($file);
    }
}
