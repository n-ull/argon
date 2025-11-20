<?php

use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;

describe('CreateOrderData', function () {
    it('can be instantiated with event id and products', function () {
        $products = [
            new CreateOrderProductData(productId: 1, selectedPriceId: 10, quantity: 2),
            new CreateOrderProductData(productId: 2, selectedPriceId: 20, quantity: 1),
        ];

        $orderData = new CreateOrderData(
            eventId: 100,
            products: $products
        );

        expect($orderData->eventId)->toBe(100)
            ->and($orderData->products)->toHaveCount(2)
            ->and($orderData->products[0])->toBeInstanceOf(CreateOrderProductData::class)
            ->and($orderData->products[0]->productId)->toBe(1)
            ->and($orderData->products[1]->productId)->toBe(2);
    });

    it('can be instantiated with empty products array', function () {
        $orderData = new CreateOrderData(
            eventId: 50,
            products: []
        );

        expect($orderData->eventId)->toBe(50)
            ->and($orderData->products)->toBeEmpty();
    });

    it('can be instantiated with single product', function () {
        $product = new CreateOrderProductData(productId: 5, selectedPriceId: 15, quantity: 3);

        $orderData = new CreateOrderData(
            eventId: 200,
            products: [$product]
        );

        expect($orderData->eventId)->toBe(200)
            ->and($orderData->products)->toHaveCount(1)
            ->and($orderData->products[0]->productId)->toBe(5)
            ->and($orderData->products[0]->selectedPriceId)->toBe(15)
            ->and($orderData->products[0]->quantity)->toBe(3);
    });

    it('maintains product order', function () {
        $products = [
            new CreateOrderProductData(productId: 3, selectedPriceId: 30, quantity: 1),
            new CreateOrderProductData(productId: 1, selectedPriceId: 10, quantity: 2),
            new CreateOrderProductData(productId: 2, selectedPriceId: 20, quantity: 3),
        ];

        $orderData = new CreateOrderData(eventId: 1, products: $products);

        expect($orderData->products[0]->productId)->toBe(3)
            ->and($orderData->products[1]->productId)->toBe(1)
            ->and($orderData->products[2]->productId)->toBe(2);
    });

    it('handles large event ids', function () {
        $orderData = new CreateOrderData(
            eventId: PHP_INT_MAX,
            products: []
        );

        expect($orderData->eventId)->toBe(PHP_INT_MAX);
    });

    it('can handle many products', function () {
        $products = [];
        for ($i = 1; $i <= 100; $i++) {
            $products[] = new CreateOrderProductData(
                productId: $i,
                selectedPriceId: $i * 10,
                quantity: $i % 5 + 1
            );
        }

        $orderData = new CreateOrderData(eventId: 1, products: $products);

        expect($orderData->products)->toHaveCount(100)
            ->and($orderData->products[0]->productId)->toBe(1)
            ->and($orderData->products[99]->productId)->toBe(100);
    });
});