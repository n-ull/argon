<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Services\OrderService;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OrderService', function () {
    test('can be instantiated with OrderValidatorService', function () {
        $validatorService = new OrderValidatorService();
        $service = new OrderService($validatorService);

        expect($service)->toBeInstanceOf(OrderService::class);
    });

    test('throws exception when event does not exist', function () {
        $validatorService = new OrderValidatorService();
        $service = new OrderService($validatorService);

        $orderData = new CreateOrderData(
            eventId: 9999,
            products: []
        );

        $service->createPendingOrder($orderData);
    })->throws(DomainException::class, "Event doesn't exist.");

    test('creates pending order for valid event', function () {
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
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 2),
            ]
        );

        $validatorService = new OrderValidatorService();
        $service = new OrderService($validatorService);

        $order = $service->createPendingOrder($orderData);

        expect($order)->toBeInstanceOf(Order::class)
            ->and($order->event_id)->toBe($event->id)
            ->and($order->exists)->toBeTrue();
    });

    test('validates order before creating', function () {
        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: []
        );

        $validatorService = new OrderValidatorService();
        $service = new OrderService($validatorService);

        $service->createPendingOrder($orderData);
    })->throws(DomainException::class, 'Event is not published.');

    test('order belongs to correct event', function () {
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
            'sort_order' => 1,
        ]);

        $orderData = new CreateOrderData(
            eventId: $event->id,
            products: [
                new CreateOrderProductData($product->id, $price->id, 1),
            ]
        );

        $validatorService = new OrderValidatorService();
        $service = new OrderService($validatorService);

        $order = $service->createPendingOrder($orderData);

        expect($event->orders->contains($order))->toBeTrue();
    });
});