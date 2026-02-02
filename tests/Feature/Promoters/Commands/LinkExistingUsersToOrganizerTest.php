<?php

namespace Tests\Feature\Promoters\Commands;

use Tests\TestCase;
use App\Models\User;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkExistingUsersToOrganizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_existing_command_messy_file()
    {
        $organizer = Organizer::factory()->create();
        $user1 = User::factory()->create(['email' => 'test1@example.com']);
        $user2 = User::factory()->create(['email' => 'test2@example.com']);

        // Create a messy file
        $file = sys_get_temp_dir().'/messy_link_emails.txt';
        $content = "Please link test1@example.com and test2@example.com to my org.\nIgnore invalid@email.";
        file_put_contents($file, $content);

        $this->artisan('promoters:link-existing', [
            'organizer' => $organizer->id,
            '--file' => $file,
        ])->assertSuccessful();

        $this->assertEquals(2, \Domain\Promoters\Models\Promoter::count(), 'Promoters were not created');


        // Refresh users to load new relationships
        $user1->refresh();
        $user2->refresh();

        $this->assertDatabaseHas('organizer_promoter', [
            'organizer_id' => $organizer->id,
            'promoter_id' => $user1->promoter->id,
        ]);

        $this->assertDatabaseHas('organizer_promoter', [
            'organizer_id' => $organizer->id,
            'promoter_id' => $user2->promoter->id,
        ]);

        unlink($file);
    }
}
