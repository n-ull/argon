<?php

use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;

describe('CreateOrderData', function () {
    test('can be instantiated with event ID and products', function () {
        $products = [
            new CreateOrderProductData(1, 2, 3),
            new CreateOrderProductData(4, 5, 6),
        ];

        $data = new CreateOrderData(
            eventId: 10,
            products: $products
        );

        expect($data)->toBeInstanceOf(CreateOrderData::class)
            ->and($data->eventId)->toBe(10)
            ->and($data->products)->toBeArray()
            ->and($data->products)->toHaveCount(2);
    });

    test('extends Spatie LaravelData Data class', function () {
        $data = new CreateOrderData(1, []);

        expect($data)->toBeInstanceOf(\Spatie\LaravelData\Data::class);
    });

    test('products array contains CreateOrderProductData instances', function () {
        $product = new CreateOrderProductData(1, 2, 3);
        $data = new CreateOrderData(1, [$product]);

        expect($data->products[0])->toBeInstanceOf(CreateOrderProductData::class)
            ->and($data->products[0]->productId)->toBe(1)
            ->and($data->products[0]->selectedPriceId)->toBe(2)
            ->and($data->products[0]->quantity)->toBe(3);
    });

    test('can handle empty products array', function () {
        $data = new CreateOrderData(1, []);

        expect($data->products)->toBeArray()
            ->and($data->products)->toBeEmpty();
    });

    test('can handle multiple products', function () {
        $products = [
            new CreateOrderProductData(1, 10, 2),
            new CreateOrderProductData(2, 20, 5),
            new CreateOrderProductData(3, 30, 1),
        ];

        $data = new CreateOrderData(100, $products);

        expect($data->products)->toHaveCount(3)
            ->and($data->products[0]->productId)->toBe(1)
            ->and($data->products[1]->productId)->toBe(2)
            ->and($data->products[2]->productId)->toBe(3);
    });
});