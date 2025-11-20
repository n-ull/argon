<?php

use Domain\ProductCatalog\Enums\ProductType;

describe('ProductType Enum', function () {
    it('has GENERAL type', function () {
        expect(ProductType::GENERAL)->toBeInstanceOf(ProductType::class)
            ->and(ProductType::GENERAL->value)->toBe('general');
    });

    it('has TICKET type', function () {
        expect(ProductType::TICKET)->toBeInstanceOf(ProductType::class)
            ->and(ProductType::TICKET->value)->toBe('ticket');
    });

    it('can get all cases', function () {
        $cases = ProductType::cases();

        expect($cases)->toHaveCount(2)
            ->and($cases)->toContain(ProductType::GENERAL)
            ->and($cases)->toContain(ProductType::TICKET);
    });

    it('can create from value', function () {
        expect(ProductType::from('general'))->toBe(ProductType::GENERAL)
            ->and(ProductType::from('ticket'))->toBe(ProductType::TICKET);
    });

    it('throws exception for invalid value', function () {
        ProductType::from('invalid');
    })->throws(ValueError::class);

    it('can use tryFrom safely', function () {
        expect(ProductType::tryFrom('general'))->toBe(ProductType::GENERAL)
            ->and(ProductType::tryFrom('ticket'))->toBe(ProductType::TICKET)
            ->and(ProductType::tryFrom('invalid'))->toBeNull();
    });

    it('types are unique', function () {
        $values = array_map(fn($case) => $case->value, ProductType::cases());
        $unique = array_unique($values);

        expect(count($values))->toBe(count($unique));
    });

    it('can be compared', function () {
        expect(ProductType::GENERAL === ProductType::GENERAL)->toBeTrue()
            ->and(ProductType::GENERAL === ProductType::TICKET)->toBeFalse()
            ->and(ProductType::GENERAL !== ProductType::TICKET)->toBeTrue();
    });
});