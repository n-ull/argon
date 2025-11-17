<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EventManagement\Actions;

use Domain\EventManagement\Actions\CreateEvent;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateEventTest extends TestCase
{
    use RefreshDatabase;

    private CreateEvent $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateEvent();
    }

    /** @test */
    public function it_creates_an_event_with_required_fields(): void
    {
        $organizer = Organizer::factory()->create();
        $locationInfo = new LocationInfo(
            venueName: 'Test Venue',
            address: '123 Test St',
            city: 'Test City',
            country: 'Test Country'
        );

        $data = [
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'slug' => 'test-event',
            'location_info' => $locationInfo,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
        ];

        $event = $this->action->execute($data);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Test Event', $event->title);
        $this->assertEquals('test-event', $event->slug);
        $this->assertEquals($organizer->id, $event->organizer_id);
        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'slug' => 'test-event',
        ]);
    }

    /** @test */
    public function it_creates_an_event_with_all_fields(): void
    {
        $organizer = Organizer::factory()->create();
        $locationInfo = new LocationInfo(
            venueName: 'Full Test Venue',
            address: '456 Full St',
            city: 'Full City',
            state: 'FC',
            postalCode: '12345',
            country: 'Full Country',
            latitude: 40.7128,
            longitude: -74.0060
        );

        $data = [
            'organizer_id' => $organizer->id,
            'title' => 'Complete Event',
            'slug' => 'complete-event',
            'description' => 'A complete event with all fields',
            'location_info' => $locationInfo,
            'starts_at' => now()->addDays(10),
            'ends_at' => now()->addDays(12),
            'registration_starts_at' => now(),
            'registration_ends_at' => now()->addDays(9),
            'max_attendees' => 100,
            'is_published' => true,
        ];

        $event = $this->action->execute($data);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Complete Event', $event->title);
        $this->assertEquals('A complete event with all fields', $event->description);
        $this->assertEquals(100, $event->max_attendees);
        $this->assertTrue($event->is_published);
        $this->assertInstanceOf(LocationInfo::class, $event->location_info);
        $this->assertEquals('Full Test Venue', $event->location_info->venueName);
    }

    /** @test */
    public function it_creates_event_without_optional_fields(): void
    {
        $organizer = Organizer::factory()->create();

        $data = [
            'organizer_id' => $organizer->id,
            'title' => 'Minimal Event',
            'slug' => 'minimal-event',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(3),
        ];

        $event = $this->action->execute($data);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertNull($event->description);
        $this->assertNull($event->location_info);
        $this->assertNull($event->max_attendees);
        $this->assertFalse($event->is_published);
    }

    /** @test */
    public function it_persists_event_to_database(): void
    {
        $organizer = Organizer::factory()->create();

        $data = [
            'organizer_id' => $organizer->id,
            'title' => 'Persistent Event',
            'slug' => 'persistent-event',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
        ];

        $event = $this->action->execute($data);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Persistent Event',
            'organizer_id' => $organizer->id,
        ]);
    }

    /** @test */
    public function it_handles_registration_date_ranges(): void
    {
        $organizer = Organizer::factory()->create();
        $regStart = now();
        $regEnd = now()->addDays(7);

        $data = [
            'organizer_id' => $organizer->id,
            'title' => 'Registration Event',
            'slug' => 'registration-event',
            'starts_at' => now()->addDays(14),
            'ends_at' => now()->addDays(16),
            'registration_starts_at' => $regStart,
            'registration_ends_at' => $regEnd,
        ];

        $event = $this->action->execute($data);

        $this->assertNotNull($event->registration_starts_at);
        $this->assertNotNull($event->registration_ends_at);
        $this->assertEquals(
            $regStart->format('Y-m-d H:i'),
            $event->registration_starts_at->format('Y-m-d H:i')
        );
        $this->assertEquals(
            $regEnd->format('Y-m-d H:i'),
            $event->registration_ends_at->format('Y-m-d H:i')
        );
    }
}