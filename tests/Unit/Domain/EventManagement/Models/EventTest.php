<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EventManagement\Models;

use Domain\EventManagement\Casts\LocationInfoJson;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $expected = [
            'title',
            'description',
            'location_info',
            'status',
            'start_date',
            'end_date',
            'organizer_id',
            'is_featured',
            'slug',
        ];

        $event = new Event;
        $this->assertEquals($expected, $event->getFillable());
    }

    /** @test */
    public function it_casts_location_info_to_location_info_json(): void
    {
        $event = new Event;
        $casts = $event->getCasts();

        $this->assertArrayHasKey('location_info', $casts);
        $this->assertEquals(LocationInfoJson::class, $casts['location_info']);
    }

    /** @test */
    public function it_casts_status_to_event_status_enum(): void
    {
        $event = new Event;
        $casts = $event->getCasts();

        $this->assertArrayHasKey('status', $casts);
        $this->assertEquals(EventStatus::class, $casts['status']);
    }

    /** @test */
    public function it_casts_datetime_fields_correctly(): void
    {
        $event = new Event;
        $casts = $event->getCasts();

        $this->assertEquals('datetime', $casts['start_date']);
        $this->assertEquals('datetime', $casts['end_date']);
    }

    /** @test */
    public function it_belongs_to_an_organizer(): void
    {
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        $this->assertInstanceOf(Organizer::class, $event->organizer);
        $this->assertEquals($organizer->id, $event->organizer->id);
    }

    /** @test */
    public function it_has_products_relationship(): void
    {
        $event = Event::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $event->products()
        );
    }

    /** @test */
    public function it_has_orders_relationship(): void
    {
        $event = Event::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $event->orders()
        );
    }

    /** @test */
    public function it_can_create_event_with_location_info(): void
    {
        $organizer = Organizer::factory()->create();
        $locationInfo = new LocationInfo(
            address: '123 Test St',
            city: 'Test City',
            country: 'Test Country'
        );

        $event = Event::factory()->create([
            'organizer_id' => $organizer->id,
            'title' => 'Test Event',
            'location_info' => $locationInfo,
        ]);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Test Event', $event->title);
        $this->assertInstanceOf(LocationInfo::class, $event->location_info);
    }

    /** @test */
    public function it_can_have_a_status(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $this->assertEquals(EventStatus::DRAFT, $event->status);

        $event->update(['status' => EventStatus::PUBLISHED]);

        $this->assertEquals(EventStatus::PUBLISHED, $event->fresh()->status);
    }

    /** @test */
    public function it_stores_event_dates(): void
    {
        $startsAt = now()->addDays(10);
        $endsAt = now()->addDays(12);

        $event = Event::factory()->create([
            'start_date' => $startsAt,
            'end_date' => $endsAt,
        ]);

        $this->assertEquals(
            $startsAt->format('Y-m-d H:i:s'),
            $event->start_date->format('Y-m-d H:i:s')
        );
        $this->assertEquals(
            $endsAt->format('Y-m-d H:i:s'),
            $event->end_date->format('Y-m-d H:i:s')
        );
    }

    /** @test */
    public function it_has_a_slug(): void
    {
        $event = Event::factory()->create(['slug' => 'test-event-slug']);

        $this->assertEquals('test-event-slug', $event->slug);
    }

    /** @test */
    public function it_can_have_a_description(): void
    {
        $description = 'This is a test event description with lots of details.';
        $event = Event::factory()->create(['description' => $description]);

        $this->assertEquals($description, $event->description);
    }

    /** @test */
    public function it_can_have_null_description(): void
    {
        $event = Event::factory()->create(['description' => null]);

        $this->assertNull($event->description);
    }

    /** @test */
    public function location_info_can_be_null(): void
    {
        $event = Event::factory()->create(['location_info' => null]);

        $this->assertNull($event->location_info);
    }

    /** @test */
    public function it_can_retrieve_event_by_slug(): void
    {
        $event = Event::factory()->create(['slug' => 'unique-event']);

        $found = Event::where('slug', 'unique-event')->first();

        $this->assertNotNull($found);
        $this->assertEquals($event->id, $found->id);
    }

    /** @test */
    public function multiple_events_can_belong_to_same_organizer(): void
    {
        $organizer = Organizer::factory()->create();

        $event1 = Event::factory()->create(['organizer_id' => $organizer->id]);
        $event2 = Event::factory()->create(['organizer_id' => $organizer->id]);

        $this->assertEquals($organizer->id, $event1->organizer_id);
        $this->assertEquals($organizer->id, $event2->organizer_id);
        $this->assertEquals(2, $organizer->events()->count());
    }

    /** @test */
    public function it_can_be_featured(): void
    {
        $event = Event::factory()->create(['is_featured' => true]);

        $this->assertTrue($event->is_featured);
    }
}
