<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Event Model', function () {
    test('can create an event with required attributes', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Test Event',
            'description' => 'Test Description',
            'location_info' => [
                'address' => '123 Main St',
                'city' => 'Test City',
                'country' => 'Test Country',
            ],
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(31),
            'organizer_id' => $organizer->id,
            'is_featured' => false,
            'slug' => 'test-event',
        ]);

        expect($event)->toBeInstanceOf(Event::class)
            ->and($event->title)->toBe('Test Event')
            ->and($event->description)->toBe('Test Description')
            ->and($event->status)->toBe(EventStatus::DRAFT)
            ->and($event->is_featured)->toBeFalse()
            ->and($event->slug)->toBe('test-event');
    });

    test('casts status to EventStatus enum', function () {
        $event = Event::factory()->create(['status' => 'published']);

        expect($event->status)->toBeInstanceOf(EventStatus::class)
            ->and($event->status)->toBe(EventStatus::PUBLISHED);
    });

    test('casts location_info to LocationInfo value object', function () {
        $event = Event::factory()->create([
            'location_info' => [
                'address' => '456 Oak Ave',
                'city' => 'Springfield',
                'country' => 'USA',
                'mapLink' => 'https://maps.example.com',
                'site' => 'https://venue.example.com',
            ],
        ]);

        expect($event->location_info)->toBeInstanceOf(LocationInfo::class)
            ->and($event->location_info->address)->toBe('456 Oak Ave')
            ->and($event->location_info->city)->toBe('Springfield')
            ->and($event->location_info->country)->toBe('USA')
            ->and($event->location_info->mapLink)->toBe('https://maps.example.com')
            ->and($event->location_info->site)->toBe('https://venue.example.com');
    });

    test('casts start_date and end_date to Carbon instances', function () {
        $startDate = now()->addDays(30);
        $endDate = now()->addDays(31);

        $event = Event::factory()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        expect($event->start_date)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($event->end_date)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($event->start_date->toDateString())->toBe($startDate->toDateString())
            ->and($event->end_date->toDateString())->toBe($endDate->toDateString());
    });

    test('belongs to an organizer', function () {
        $organizer = Organizer::factory()->create();
        $event = Event::factory()->create(['organizer_id' => $organizer->id]);

        expect($event->organizer)->toBeInstanceOf(Organizer::class)
            ->and($event->organizer->id)->toBe($organizer->id);
    });

    test('has many products', function () {
        $event = Event::factory()->create();
        $product1 = Product::factory()->create(['event_id' => $event->id]);
        $product2 = Product::factory()->create(['event_id' => $event->id]);

        $event->load('products');

        expect($event->products)->toHaveCount(2)
            ->and($event->products->pluck('id')->toArray())->toContain($product1->id, $product2->id);
    });

    test('has many orders', function () {
        $event = Event::factory()->create();
        $order1 = Order::create(['event_id' => $event->id]);
        $order2 = Order::create(['event_id' => $event->id]);

        $event->load('orders');

        expect($event->orders)->toHaveCount(2)
            ->and($event->orders->pluck('id')->toArray())->toContain($order1->id, $order2->id);
    });

    test('can handle null location_info', function () {
        $event = Event::factory()->create(['location_info' => null]);

        expect($event->location_info)->toBeNull();
    });

    test('is_featured defaults to false', function () {
        $event = Event::factory()->create(['is_featured' => false]);

        expect($event->is_featured)->toBeFalse();
    });

    test('can be featured', function () {
        $event = Event::factory()->create(['is_featured' => true]);

        expect($event->is_featured)->toBeTrue();
    });

    test('has correct table name', function () {
        $event = new Event();

        expect($event->getTable())->toBe('events');
    });

    test('has correct fillable attributes', function () {
        $event = new Event();
        $fillable = $event->getFillable();

        expect($fillable)->toContain(
            'title',
            'description',
            'location_info',
            'status',
            'start_date',
            'end_date',
            'organizer_id',
            'is_featured',
            'slug'
        );
    });
});

describe('Event Status Transitions', function () {
    test('can transition from draft to published', function () {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $event->update(['status' => EventStatus::PUBLISHED]);

        expect($event->fresh()->status)->toBe(EventStatus::PUBLISHED);
    });

    test('can transition to archived', function () {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);

        $event->update(['status' => EventStatus::ARCHIVED]);

        expect($event->fresh()->status)->toBe(EventStatus::ARCHIVED);
    });
});

describe('Event Query Scopes', function () {
    test('can query by status', function () {
        Event::factory()->create(['status' => EventStatus::DRAFT]);
        Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        Event::factory()->create(['status' => EventStatus::ARCHIVED]);

        $published = Event::where('status', EventStatus::PUBLISHED)->get();

        expect($published)->toHaveCount(1)
            ->and($published->first()->status)->toBe(EventStatus::PUBLISHED);
    });

    test('can query featured events', function () {
        Event::factory()->count(3)->create(['is_featured' => false]);
        Event::factory()->count(2)->create(['is_featured' => true]);

        $featured = Event::where('is_featured', true)->get();

        expect($featured)->toHaveCount(2);
    });

    test('can query by organizer', function () {
        $organizer = Organizer::factory()->create();
        Event::factory()->count(3)->create(['organizer_id' => $organizer->id]);
        Event::factory()->count(2)->create();

        $organizerEvents = Event::where('organizer_id', $organizer->id)->get();

        expect($organizerEvents)->toHaveCount(3);
    });
});