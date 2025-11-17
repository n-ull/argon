<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OrderValidatorService', function () {
    test('can be instantiated', function () {
        $service = new OrderValidatorService();

        expect($service)->toBeInstanceOf(OrderValidatorService::class);
    });

    test('validates order successfully for published event', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'quantity_sold' => 0,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData(
                    productId: $product->id,
                    selectedPriceId: $price->id,
                    quantity: 5
                ),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);

        expect(true)->toBeTrue(); // No exception thrown
    });

    test('throws exception when event is not published', function () {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: []
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Event is not published.');

    test('throws exception when event is archived', function () {
        $event = Event::factory()->create(['status' => EventStatus::ARCHIVED]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: []
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Event is not published.');

    test('throws exception when product does not exist in event', function () {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $otherEvent = Event::factory()->create();
        $product = Product::factory()->create(['event_id' => $otherEvent->id]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, 1, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Event product doesn\'t exist');

    test('throws exception when selected price does not exist', function () {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $product = Product::factory()->create(['event_id' => $event->id]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, 999, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Selected price doesn\'t exist');

    test('throws exception when product sales have not started', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(60),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->addDays(10),
            'end_sale_date' => now()->addDays(50),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Product sales are not available.');

    test('throws exception when product sales have ended', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDays(60),
            'end_date' => now()->addDays(30),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDays(50),
            'end_sale_date' => now()->subDay(),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Product sales are not available.');

    test('throws exception when price sales have not started', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Early Bird',
            'stock' => 50,
            'start_sale_date' => now()->addDays(5),
            'end_sale_date' => now()->addDays(10),
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Product with this price sales are not available');

    test('throws exception when price sales have ended', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDays(30),
            'end_date' => now()->addDays(30),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDays(30),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 50.00,
            'label' => 'Early Bird',
            'stock' => 50,
            'start_sale_date' => now()->subDays(20),
            'end_sale_date' => now()->subDay(),
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Product with this price sales are not available');

    test('throws exception when stock is insufficient', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 5,
            'quantity_sold' => 0,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 10),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);
    })->throws(DomainException::class, 'Product is not available');

    test('validates multiple products successfully', function () {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
        ]);

        $product1 = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price1 = ProductPrice::create([
            'product_id' => $product1->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'sort_order' => 1,
        ]);

        $product2 = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDay(),
            'end_sale_date' => now()->addDays(30),
        ]);

        $price2 = ProductPrice::create([
            'product_id' => $product2->id,
            'price' => 200.00,
            'label' => 'VIP',
            'stock' => 20,
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product1->id, $price1->id, 2),
                new CreateOrderProductData($product2->id, $price2->id, 1),
            ]
        );

        $service = new OrderValidatorService();
        $service->validateOrder($orderData);

        expect(true)->toBeTrue(); // No exception thrown
    });
});