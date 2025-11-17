<?php

use Domain\ProductCatalog\Enums\ProductPriceType;

describe('ProductPriceType Enum', function () {
    test('has standard case', function () {
        expect(ProductPriceType::STANDARD->value)->toBe('standard');
    });

    test('has free case', function () {
        expect(ProductPriceType::FREE->value)->toBe('free');
    });

    test('has staggered case', function () {
        expect(ProductPriceType::STAGGERED->value)->toBe('staggered');
    });

    test('can be instantiated from string value', function () {
        $type = ProductPriceType::from('standard');

        expect($type)->toBe(ProductPriceType::STANDARD);
    });

    test('can get all cases', function () {
        $cases = ProductPriceType::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(
                ProductPriceType::STANDARD,
                ProductPriceType::FREE,
                ProductPriceType::STAGGERED
            );
    });

    test('can try from with valid value', function () {
        $type = ProductPriceType::tryFrom('free');

        expect($type)->toBe(ProductPriceType::FREE);
    });

    test('returns null for invalid value with tryFrom', function () {
        $type = ProductPriceType::tryFrom('invalid');

        expect($type)->toBeNull();
    });

    test('throws exception for invalid value with from', function () {
        ProductPriceType::from('invalid');
    })->throws(ValueError::class);

    test('can be compared', function () {
        expect(ProductPriceType::STANDARD === ProductPriceType::STANDARD)->toBeTrue()
            ->and(ProductPriceType::STANDARD === ProductPriceType::FREE)->toBeFalse();
    });

    test('can be used in match expression', function () {
        $result = match (ProductPriceType::STAGGERED) {
            ProductPriceType::STANDARD => 'standard pricing',
            ProductPriceType::FREE => 'free product',
            ProductPriceType::STAGGERED => 'staggered pricing',
        };

        expect($result)->toBe('staggered pricing');
    });
});