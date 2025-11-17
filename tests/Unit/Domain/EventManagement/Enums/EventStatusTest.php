<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\EventManagement\Enums;

use Domain\EventManagement\Enums\EventStatus;
use PHPUnit\Framework\TestCase;

class EventStatusTest extends TestCase
{
    /** @test */
    public function it_has_draft_status(): void
    {
        $status = EventStatus::DRAFT;

        $this->assertEquals('draft', $status->value);
        $this->assertInstanceOf(EventStatus::class, $status);
    }

    /** @test */
    public function it_has_published_status(): void
    {
        $status = EventStatus::PUBLISHED;

        $this->assertEquals('published', $status->value);
        $this->assertInstanceOf(EventStatus::class, $status);
    }

    /** @test */
    public function it_has_archived_status(): void
    {
        $status = EventStatus::ARCHIVED;

        $this->assertEquals('archived', $status->value);
        $this->assertInstanceOf(EventStatus::class, $status);
    }

    /** @test */
    public function it_can_be_created_from_value(): void
    {
        $status = EventStatus::from('draft');

        $this->assertEquals(EventStatus::DRAFT, $status);
    }

    /** @test */
    public function it_can_retrieve_all_cases(): void
    {
        $cases = EventStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(EventStatus::DRAFT, $cases);
        $this->assertContains(EventStatus::PUBLISHED, $cases);
        $this->assertContains(EventStatus::ARCHIVED, $cases);
    }

    /** @test */
    public function it_can_be_compared(): void
    {
        $status1 = EventStatus::DRAFT;
        $status2 = EventStatus::DRAFT;
        $status3 = EventStatus::PUBLISHED;

        $this->assertTrue($status1 === $status2);
        $this->assertFalse($status1 === $status3);
    }

    /** @test */
    public function it_can_try_from_with_valid_value(): void
    {
        $status = EventStatus::tryFrom('published');

        $this->assertNotNull($status);
        $this->assertEquals(EventStatus::PUBLISHED, $status);
    }

    /** @test */
    public function it_returns_null_for_invalid_value(): void
    {
        $status = EventStatus::tryFrom('invalid');

        $this->assertNull($status);
    }
}
