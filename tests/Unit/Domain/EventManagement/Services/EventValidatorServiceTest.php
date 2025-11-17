<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EventManagement\Services;

use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Services\EventValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventValidatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventValidatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EventValidatorService;
    }

    /** @test */
    public function it_passes_validation_when_start_date_is_before_end_date(): void
    {
        $event = Event::factory()->create([
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        $this->service->validate($event);

        $this->assertTrue(true); // No exception thrown
    }

    /** @test */
    public function it_throws_exception_when_start_date_is_after_end_date(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Event start must be before end');

        $event = Event::factory()->create([
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(5),
        ]);

        $this->service->validate($event);
    }

    /** @test */
    public function it_throws_exception_when_start_date_equals_end_date(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Event start must be before end');

        $sameDate = now()->addDays(5);
        $event = Event::factory()->create([
            'start_date' => $sameDate,
            'end_date' => $sameDate,
        ]);

        $this->service->validate($event);
    }

    /** @test */
    public function it_validates_events_spanning_multiple_days(): void
    {
        $event = Event::factory()->create([
            'start_date' => now(),
            'end_date' => now()->addDays(30),
        ]);

        $this->service->validate($event);

        $this->assertTrue(true); // No exception thrown
    }

    /** @test */
    public function it_validates_events_with_same_day_but_different_times(): void
    {
        $baseDate = now()->startOfDay();
        $event = Event::factory()->create([
            'start_date' => $baseDate->copy()->addHours(10),
            'end_date' => $baseDate->copy()->addHours(18),
        ]);

        $this->service->validate($event);

        $this->assertTrue(true); // No exception thrown
    }
}
