<?php

use Domain\ProductCatalog\Enums\ProductPriceType;

describe('ProductPriceType Enum', function () {
    it('has STANDARD type', function () {
        expect(ProductPriceType::STANDARD)->toBeInstanceOf(ProductPriceType::class)
            ->and(ProductPriceType::STANDARD->value)->toBe('standard');
    });

    it('has FREE type', function () {
        expect(ProductPriceType::FREE)->toBeInstanceOf(ProductPriceType::class)
            ->and(ProductPriceType::FREE->value)->toBe('free');
    });

    it('has STAGGERED type', function () {
        expect(ProductPriceType::STAGGERED)->toBeInstanceOf(ProductPriceType::class)
            ->and(ProductPriceType::STAGGERED->value)->toBe('staggered');
    });

    it('can get all cases', function () {
        $cases = ProductPriceType::cases();

        expect($cases)->toHaveCount(3)
            ->and($cases)->toContain(ProductPriceType::STANDARD)
            ->and($cases)->toContain(ProductPriceType::FREE)
            ->and($cases)->toContain(ProductPriceType::STAGGERED);
    });

    it('can create from value', function () {
        expect(ProductPriceType::from('standard'))->toBe(ProductPriceType::STANDARD)
            ->and(ProductPriceType::from('free'))->toBe(ProductPriceType::FREE)
            ->and(ProductPriceType::from('staggered'))->toBe(ProductPriceType::STAGGERED);
    });

    it('throws exception for invalid value', function () {
        ProductPriceType::from('invalid');
    })->throws(ValueError::class);

    it('can use tryFrom safely', function () {
        expect(ProductPriceType::tryFrom('standard'))->toBe(ProductPriceType::STANDARD)
            ->and(ProductPriceType::tryFrom('free'))->toBe(ProductPriceType::FREE)
            ->and(ProductPriceType::tryFrom('staggered'))->toBe(ProductPriceType::STAGGERED)
            ->and(ProductPriceType::tryFrom('invalid'))->toBeNull();
    });

    it('types are unique', function () {
        $values = array_map(fn($case) => $case->value, ProductPriceType::cases());
        $unique = array_unique($values);

        expect(count($values))->toBe(count($unique));
    });

    it('can be compared', function () {
        expect(ProductPriceType::STANDARD === ProductPriceType::STANDARD)->toBeTrue()
            ->and(ProductPriceType::STANDARD === ProductPriceType::FREE)->toBeFalse()
            ->and(ProductPriceType::FREE !== ProductPriceType::STAGGERED)->toBeTrue();
    });
});