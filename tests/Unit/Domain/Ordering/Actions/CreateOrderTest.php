<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Actions\CreateOrder;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use Domain\Ordering\Services\OrderService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CreateOrder Action', function () {
    test('can be instantiated with OrderService', function () {
        $orderService = app(OrderService::class);
        $action = new CreateOrder($orderService);

        expect($action)->toBeInstanceOf(CreateOrder::class);
    });

    test('uses AsAction trait', function () {
        expect(class_uses(CreateOrder::class))->toContain(\Lorisleiva\Actions\Concerns\AsAction::class);
    });

    test('handle method delegates to order service', function () {
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

        $orderService = app(OrderService::class);
        $action = new CreateOrder($orderService);

        $action->handle($orderData);

        expect($event->orders()->count())->toBe(1);
    });
});