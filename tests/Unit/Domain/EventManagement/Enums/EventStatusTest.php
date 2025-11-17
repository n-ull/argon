<?php

use Domain\EventManagement\Enums\EventStatus;

describe('EventStatus Enum', function () {
    test('has draft case', function () {
        expect(EventStatus::DRAFT->value)->toBe('draft');
    });

    test('has published case', function () {
        expect(EventStatus::PUBLISHED->value)->toBe('published');
    });

    test('has archived case', function () {
        expect(EventStatus::ARCHIVED->value)->toBe('archived');
    });

    test('can be instantiated from string value', function () {
        $status = EventStatus::from('draft');

        expect($status)->toBe(EventStatus::DRAFT);
    });

    test('can get all cases', function () {
        $cases = EventStatus::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(EventStatus::DRAFT, EventStatus::PUBLISHED, EventStatus::ARCHIVED);
    });

    test('can try from with valid value', function () {
        $status = EventStatus::tryFrom('published');

        expect($status)->toBe(EventStatus::PUBLISHED);
    });

    test('returns null for invalid value with tryFrom', function () {
        $status = EventStatus::tryFrom('invalid');

        expect($status)->toBeNull();
    });

    test('throws exception for invalid value with from', function () {
        EventStatus::from('invalid');
    })->throws(ValueError::class);

    test('can be compared', function () {
        expect(EventStatus::DRAFT === EventStatus::DRAFT)->toBeTrue()
            ->and(EventStatus::DRAFT === EventStatus::PUBLISHED)->toBeFalse();
    });

    test('can be used in match expression', function () {
        $result = match (EventStatus::PUBLISHED) {
            EventStatus::DRAFT => 'draft',
            EventStatus::PUBLISHED => 'published',
            EventStatus::ARCHIVED => 'archived',
        };

        expect($result)->toBe('published');
    });
});