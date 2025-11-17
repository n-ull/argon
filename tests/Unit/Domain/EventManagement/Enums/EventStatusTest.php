<?php

use Domain\EventManagement\Enums\EventStatus;

describe('EventStatus Enum', function () {
    it('has DRAFT status', function () {
        expect(EventStatus::DRAFT)->toBeInstanceOf(EventStatus::class)
            ->and(EventStatus::DRAFT->value)->toBe('draft');
    });

    it('has PUBLISHED status', function () {
        expect(EventStatus::PUBLISHED)->toBeInstanceOf(EventStatus::class)
            ->and(EventStatus::PUBLISHED->value)->toBe('published');
    });

    it('has ARCHIVED status', function () {
        expect(EventStatus::ARCHIVED)->toBeInstanceOf(EventStatus::class)
            ->and(EventStatus::ARCHIVED->value)->toBe('archived');
    });

    it('can get all cases', function () {
        $cases = EventStatus::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(EventStatus::DRAFT)
            ->and($cases)->toContain(EventStatus::PUBLISHED)
            ->and($cases)->toContain(EventStatus::ARCHIVED);
    });

    it('can create from value', function () {
        expect(EventStatus::from('draft'))->toBe(EventStatus::DRAFT)
            ->and(EventStatus::from('published'))->toBe(EventStatus::PUBLISHED)
            ->and(EventStatus::from('archived'))->toBe(EventStatus::ARCHIVED);
    });

    it('throws exception for invalid value', function () {
        EventStatus::from('invalid');
    })->throws(ValueError::class);

    it('can use tryFrom safely', function () {
        expect(EventStatus::tryFrom('draft'))->toBe(EventStatus::DRAFT)
            ->and(EventStatus::tryFrom('published'))->toBe(EventStatus::PUBLISHED)
            ->and(EventStatus::tryFrom('invalid'))->toBeNull();
    });

    it('statuses are unique', function () {
        $values = array_map(fn($case) => $case->value, EventStatus::cases());
        $unique = array_unique($values);

        expect(count($values))->toBe(count($unique));
    });

    it('can be compared', function () {
        expect(EventStatus::DRAFT === EventStatus::DRAFT)->toBeTrue()
            ->and(EventStatus::DRAFT === EventStatus::PUBLISHED)->toBeFalse()
            ->and(EventStatus::PUBLISHED !== EventStatus::ARCHIVED)->toBeTrue();
    });
});