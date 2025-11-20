<?php

use Domain\Ordering\Data\CreateOrderProductData;

describe('CreateOrderProductData', function () {
    it('can be instantiated with all properties', function () {
        $productData = new CreateOrderProductData(
            productId: 42,
            selectedPriceId: 100,
            quantity: 5
        );

        expect($productData->productId)->toBe(42)
            ->and($productData->selectedPriceId)->toBe(100)
            ->and($productData->quantity)->toBe(5);
    });

    it('handles minimum quantity of 1', function () {
        $productData = new CreateOrderProductData(
            productId: 1,
            selectedPriceId: 10,
            quantity: 1
        );

        expect($productData->quantity)->toBe(1);
    });

    it('handles large quantities', function () {
        $productData = new CreateOrderProductData(
            productId: 5,
            selectedPriceId: 50,
            quantity: 9999
        );

        expect($productData->quantity)->toBe(9999);
    });

    it('stores all integer values correctly', function () {
        $productData = new CreateOrderProductData(
            productId: 123,
            selectedPriceId: 456,
            quantity: 789
        );

        expect($productData->productId)->toBeInt()
            ->and($productData->selectedPriceId)->toBeInt()
            ->and($productData->quantity)->toBeInt()
            ->and($productData->productId)->toBe(123)
            ->and($productData->selectedPriceId)->toBe(456)
            ->and($productData->quantity)->toBe(789);
    });

    it('can create multiple instances with different values', function () {
        $product1 = new CreateOrderProductData(productId: 1, selectedPriceId: 10, quantity: 2);
        $product2 = new CreateOrderProductData(productId: 2, selectedPriceId: 20, quantity: 3);

        expect($product1->productId)->not->toBe($product2->productId)
            ->and($product1->selectedPriceId)->not->toBe($product2->selectedPriceId)
            ->and($product1->quantity)->not->toBe($product2->quantity);
    });

    it('maintains data integrity', function () {
        $productData = new CreateOrderProductData(
            productId: 999,
            selectedPriceId: 888,
            quantity: 777
        );

        // Access multiple times to ensure values don't change
        expect($productData->productId)->toBe(999);
        expect($productData->productId)->toBe(999);
        expect($productData->selectedPriceId)->toBe(888);
        expect($productData->selectedPriceId)->toBe(888);
        expect($productData->quantity)->toBe(777);
        expect($productData->quantity)->toBe(777);
    });
});