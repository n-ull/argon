<?php

use Domain\ProductCatalog\Enums\ProductType;

describe('ProductType Enum', function () {
    test('has general case', function () {
        expect(ProductType::GENERAL->value)->toBe('general');
    });

    test('has ticket case', function () {
        expect(ProductType::TICKET->value)->toBe('ticket');
    });

    test('can be instantiated from string value', function () {
        $type = ProductType::from('general');

        expect($type)->toBe(ProductType::GENERAL);
    });

    test('can get all cases', function () {
        $cases = ProductType::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(ProductType::GENERAL, ProductType::TICKET);
    });

    test('can try from with valid value', function () {
        $type = ProductType::tryFrom('ticket');

        expect($type)->toBe(ProductType::TICKET);
    });

    test('returns null for invalid value with tryFrom', function () {
        $type = ProductType::tryFrom('invalid');

        expect($type)->toBeNull();
    });

    test('throws exception for invalid value with from', function () {
        ProductType::from('invalid');
    })->throws(ValueError::class);

    test('can be compared', function () {
        expect(ProductType::GENERAL === ProductType::GENERAL)->toBeTrue()
            ->and(ProductType::GENERAL === ProductType::TICKET)->toBeFalse();
    });

    test('can be used in match expression', function () {
        $result = match (ProductType::TICKET) {
            ProductType::GENERAL => 'general product',
            ProductType::TICKET => 'ticket product',
        };

        expect($result)->toBe('ticket product');
    });
});