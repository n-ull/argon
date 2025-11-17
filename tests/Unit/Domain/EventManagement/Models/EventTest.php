<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\ValueObjects\LocationInfo;
use Domain\Ordering\Models\Order;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Event Model', function () {
    it('can be created with required attributes', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Test Event',
            'slug' => 'test-event',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        expect($event)->toBeInstanceOf(Event::class)
            ->and($event->title)->toBe('Test Event')
            ->and($event->slug)->toBe('test-event')
            ->and($event->status)->toBe(EventStatus::DRAFT)
            ->and($event->organizer_id)->toBe($organizer->id);
    });

    it('can be created with all attributes including description and location', function () {
        $organizer = Organizer::factory()->create();
        
        $locationData = [
            'address' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA',
            'mapLink' => 'https://maps.example.com',
            'site' => 'venue.com',
        ];

        $event = Event::create([
            'title' => 'Complete Event',
            'description' => 'A comprehensive event description',
            'slug' => 'complete-event',
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
            'location_info' => $locationData,
            'is_featured' => true,
        ]);

        expect($event->title)->toBe('Complete Event')
            ->and($event->description)->toBe('A comprehensive event description')
            ->and($event->is_featured)->toBeTrue()
            ->and($event->location_info)->toBeInstanceOf(LocationInfo::class)
            ->and($event->location_info->address)->toBe('123 Main St');
    });

    it('casts status to EventStatus enum', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Status Test',
            'slug' => 'status-test',
            'status' => 'published',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        expect($event->status)->toBeInstanceOf(EventStatus::class)
            ->and($event->status)->toBe(EventStatus::PUBLISHED);
    });

    it('casts dates to Carbon instances', function () {
        $organizer = Organizer::factory()->create();
        $startDate = now()->addDays(7);
        $endDate = now()->addDays(8);

        $event = Event::create([
            'title' => 'Date Test',
            'slug' => 'date-test',
            'status' => EventStatus::DRAFT,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'organizer_id' => $organizer->id,
        ]);

        expect($event->start_date)->toBeInstanceOf(\Carbon\Carbon::class)
            ->and($event->end_date)->toBeInstanceOf(\Carbon\Carbon::class);
    });

    it('casts location_info to LocationInfo value object', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Location Test',
            'slug' => 'location-test',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
            'location_info' => [
                'address' => '456 Oak Ave',
                'city' => 'Boston',
                'country' => 'USA',
                'mapLink' => null,
                'site' => null,
            ],
        ]);

        $event->refresh();

        expect($event->location_info)->toBeInstanceOf(LocationInfo::class)
            ->and($event->location_info->address)->toBe('456 Oak Ave')
            ->and($event->location_info->city)->toBe('Boston');
    });

    it('belongs to an organizer', function () {
        $organizer = Organizer::factory()->create(['name' => 'Test Organizer']);
        
        $event = Event::create([
            'title' => 'Organizer Test',
            'slug' => 'organizer-test',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        expect($event->organizer)->toBeInstanceOf(Organizer::class)
            ->and($event->organizer->name)->toBe('Test Organizer')
            ->and($event->organizer_id)->toBe($organizer->id);
    });

    it('has many products', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Products Test',
            'slug' => 'products-test',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        Product::create([
            'name' => 'Product 1',
            'event_id' => $event->id,
            'product_type' => 'ticket',
        ]);

        Product::create([
            'name' => 'Product 2',
            'event_id' => $event->id,
            'product_type' => 'general',
        ]);

        expect($event->products)->toHaveCount(2)
            ->and($event->products->first())->toBeInstanceOf(Product::class)
            ->and($event->products->pluck('name')->toArray())->toContain('Product 1', 'Product 2');
    });

    it('has many orders', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Orders Test',
            'slug' => 'orders-test',
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        Order::create([
            'event_id' => $event->id,
            'status' => 'pending',
            'total_before_additions' => '100.00',
            'total_gross' => '100.00',
            'expires_at' => now()->addHours(1),
        ]);

        Order::create([
            'event_id' => $event->id,
            'status' => 'completed',
            'total_before_additions' => '200.00',
            'total_gross' => '200.00',
            'expires_at' => now()->addHours(2),
        ]);

        expect($event->orders)->toHaveCount(2)
            ->and($event->orders->first())->toBeInstanceOf(Order::class);
    });

    it('can handle null description', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'No Description',
            'slug' => 'no-description',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
            'description' => null,
        ]);

        expect($event->description)->toBeNull();
    });

    it('can handle null location_info', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'No Location',
            'slug' => 'no-location',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
            'location_info' => null,
        ]);

        expect($event->location_info)->toBeNull();
    });

    it('defaults is_featured to false', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Featured Test',
            'slug' => 'featured-test',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        expect($event->is_featured)->toBeFalsy();
    });

    it('can be updated', function () {
        $organizer = Organizer::factory()->create();
        
        $event = Event::create([
            'title' => 'Original Title',
            'slug' => 'original-slug',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
        ]);

        $event->update([
            'title' => 'Updated Title',
            'status' => EventStatus::PUBLISHED,
        ]);

        expect($event->title)->toBe('Updated Title')
            ->and($event->status)->toBe(EventStatus::PUBLISHED);
    });

    it('persists location info correctly', function () {
        $organizer = Organizer::factory()->create();
        
        $locationData = [
            'address' => 'Persistence Test St',
            'city' => 'Test City',
            'country' => 'Test Country',
            'mapLink' => 'https://test.map',
            'site' => 'test.site',
        ];

        $event = Event::create([
            'title' => 'Persistence Test',
            'slug' => 'persistence-test',
            'status' => EventStatus::DRAFT,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'organizer_id' => $organizer->id,
            'location_info' => $locationData,
        ]);

        $reloaded = Event::find($event->id);

        expect($reloaded->location_info)->toBeInstanceOf(LocationInfo::class)
            ->and($reloaded->location_info->address)->toBe('Persistence Test St')
            ->and($reloaded->location_info->city)->toBe('Test City')
            ->and($reloaded->location_info->mapLink)->toBe('https://test.map');
    });
});