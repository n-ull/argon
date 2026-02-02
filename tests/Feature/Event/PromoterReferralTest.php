<?php

namespace Tests\Feature\Event;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\Promoters\Models\Promoter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoterReferralTest extends TestCase
{
    use RefreshDatabase;

    public function test_promoter_code_is_stored_in_session_if_valid()
    {
        $organizer = Organizer::factory()->create();
        $promoterUser = User::factory()->create();
        $promoter = Promoter::factory()->create([
            'user_id' => $promoterUser->id,
            'referral_code' => 'TESTCODE'
        ]);

        // Attach promoter to organizer explicitly via pivot
        $organizer->promoters()->attach($promoter->id, ['enabled' => true]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->get(route('events.show', [
            'event' => $event->slug,
            'referr' => 'TESTCODE'
        ]));

        $this->assertEquals('TESTCODE', session('referral_code_'.$event->id));
    }

    public function test_promoter_code_is_not_stored_if_promoter_not_linked()
    {
        $organizer = Organizer::factory()->create();
        $promoterUser = User::factory()->create();
        $promoter = Promoter::factory()->create([
            'user_id' => $promoterUser->id,
            'referral_code' => 'TESTCODE'
        ]);

        // Do NOT attach promoter

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->get(route('events.show', [
            'event' => $event->slug,
            'referr' => 'TESTCODE'
        ]));

        $this->assertNull(session('referral_code_'.$event->id));
    }

    public function test_promoter_code_is_not_stored_if_promoter_disabled()
    {
        $organizer = Organizer::factory()->create();
        $promoterUser = User::factory()->create();
        $promoter = Promoter::factory()->create([
            'user_id' => $promoterUser->id,
            'referral_code' => 'TESTCODE'
        ]);

        // Attach promoter but DISABLED
        $organizer->promoters()->attach($promoter->id, ['enabled' => false]);

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id
        ]);

        $this->get(route('events.show', [
            'event' => $event->slug,
            'referr' => 'TESTCODE'
        ]));

        $this->assertNull(session('referral_code_'.$event->id));
    }
}
