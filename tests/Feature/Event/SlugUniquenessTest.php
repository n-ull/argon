<?php

use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\EventManagement\Services\EventManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create two events with the same title and they get unique slugs', function () {
    $organizer = Organizer::factory()->create();
    $service = app(EventManagerService::class);

    $title = 'My Awesome Event';

    // Create the first event
    $event1 = $service->createEvent([
        'title' => $title,
        'description' => 'First event description',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
        'organizer_id' => $organizer->id,
    ]);

    expect($event1->slug)->toBe('my-awesome-event');

    // Create the second event with the same title
    $event2 = $service->createEvent([
        'title' => $title,
        'description' => 'Second event description',
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(4),
        'organizer_id' => $organizer->id,
    ]);

    expect($event2->slug)->toBe('my-awesome-event-1');

    // Create the third event with the same title
    $event3 = $service->createEvent([
        'title' => $title,
        'description' => 'Third event description',
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(6),
        'organizer_id' => $organizer->id,
    ]);

    expect($event3->slug)->toBe('my-awesome-event-2');
});

test('updating an event with the same title keeps the same slug', function () {
    $organizer = Organizer::factory()->create();
    $service = app(EventManagerService::class);

    $title = 'Unique Title';
    $event = $service->createEvent([
        'title' => $title,
        'description' => 'Description',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
        'organizer_id' => $organizer->id,
    ]);

    $originalSlug = $event->slug;

    // Simulate update through the service method (if we had one, but we use the helper)
    $newSlug = $service->generateUniqueSlug($title, $event->id);

    expect($newSlug)->toBe($originalSlug);
});

test('updating an event to a title already taken by another event generates a unique slug', function () {
    $organizer = Organizer::factory()->create();
    $service = app(EventManagerService::class);

    $event1 = $service->createEvent([
        'title' => 'Event One',
        'description' => 'Description',
        'start_date' => now()->addDays(1),
        'end_date' => now()->addDays(2),
        'organizer_id' => $organizer->id,
    ]);

    $event2 = $service->createEvent([
        'title' => 'Event Two',
        'description' => 'Description',
        'start_date' => now()->addDays(3),
        'end_date' => now()->addDays(4),
        'organizer_id' => $organizer->id,
    ]);

    // Update event 2 to have the same title as event 1
    $newSlug = $service->generateUniqueSlug('Event One', $event2->id);

    expect($newSlug)->toBe('event-one-1');
});
