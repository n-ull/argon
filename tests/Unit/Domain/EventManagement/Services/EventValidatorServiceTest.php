<?php

use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Services\EventValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('EventValidatorService', function () {
    test('validates event with valid dates', function () {
        $service = new EventValidatorService();
        $event = Event::factory()->create([
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(31),
        ]);

        $service->validate($event);

        expect(true)->toBeTrue(); // No exception thrown
    });

    test('throws exception when start date is after end date', function () {
        $service = new EventValidatorService();
        $event = Event::factory()->make([
            'start_date' => now()->addDays(31),
            'end_date' => now()->addDays(30),
        ]);

        $service->validate($event);
    })->throws(DomainException::class, 'Event start must be before end');

    test('allows same start and end date', function () {
        $service = new EventValidatorService();
        $date = now()->addDays(30);
        $event = Event::factory()->create([
            'start_date' => $date,
            'end_date' => $date,
        ]);

        $service->validate($event);

        expect(true)->toBeTrue(); // No exception thrown
    });

    test('validates event with dates far apart', function () {
        $service = new EventValidatorService();
        $event = Event::factory()->create([
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(365),
        ]);

        $service->validate($event);

        expect(true)->toBeTrue();
    });
});