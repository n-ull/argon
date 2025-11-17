<?php

use Domain\Ordering\Data\CreateOrderProductData;

describe('CreateOrderProductData', function () {
    test('can be instantiated with required properties', function () {
        $data = new CreateOrderProductData(
            productId: 1,
            selectedPriceId: 2,
            quantity: 5
        );

        expect($data)->toBeInstanceOf(CreateOrderProductData::class)
            ->and($data->productId)->toBe(1)
            ->and($data->selectedPriceId)->toBe(2)
            ->and($data->quantity)->toBe(5);
    });

    test('extends Spatie LaravelData Data class', function () {
        $data = new CreateOrderProductData(1, 2, 3);

        expect($data)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
    });

    test('can handle different quantity values', function () {
        $data1 = new CreateOrderProductData(1, 2, 1);
        $data2 = new CreateOrderProductData(1, 2, 100);

        expect($data1->quantity)->toBe(1)
            ->and($data2->quantity)->toBe(100);
    });

    test('can handle different product and price IDs', function () {
        $data = new CreateOrderProductData(
            productId: 999,
            selectedPriceId: 888,
            quantity: 5
        );

        expect($data->productId)->toBe(999)
            ->and($data->selectedPriceId)->toBe(888);
    });
});