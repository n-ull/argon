<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;

beforeEach(function () {
    $this->service = new OrderValidatorService;
});

test('it validates order successfully with valid data', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addDays(30),
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 2,
            ],

        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it throws exception when event is not published', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::DRAFT,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: []
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Event is not published.');

test('it throws exception when product does not belong to event', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
    ]);

    $otherEvent = Event::factory()->create();
    $product = Product::factory()->create([
        'event_id' => $otherEvent->id,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => 1,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Event product doesn\'t exist');

test('it throws exception when selected price does not exist', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => 999,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Selected price doesn\'t exist');

test('it throws exception when product sale has not started', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(30),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => now()->addDays(2),
        'end_sale_date' => now()->addDays(30),
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Product sales are not available.');

test('it throws exception when product sale has ended', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDays(30),
        'end_date' => now()->addDays(30),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => now()->subDays(10),
        'end_sale_date' => now()->subDay(),
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Product sales are not available.');

test('it uses event start date when product has no start sale date', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => null,
        'end_sale_date' => now()->addDays(30),
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it uses event end date when product has no end sale date', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => null,
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it throws exception when price sale has not started', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Early Bird',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
        'start_sale_date' => now()->addDays(2),
        'end_sale_date' => now()->addDays(10),
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Product with this price sales are not available');

test('it throws exception when price sale has ended', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Early Bird',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
        'start_sale_date' => now()->subDays(10),
        'end_sale_date' => now()->subDay(),
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Product with this price sales are not available');

test('it uses event start date when price has no start sale date', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
        'start_sale_date' => null,
        'end_sale_date' => now()->addDays(10),
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it uses event end date when price has no end sale date', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => null,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it throws exception when stock is insufficient', function () {
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

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 5,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 10,
            ],
        ]
    );

    $this->service->validateOrder($orderData);
})->throws(DomainException::class, 'Product is not available');

test('it validates multiple items in single order', function () {
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

    $productPrice1 = ProductPrice::create([
        'product_id' => $product1->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $product2 = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addDays(30),
    ]);

    $productPrice2 = ProductPrice::create([
        'product_id' => $product2->id,
        'price' => 50.00,
        'label' => 'VIP',
        'stock' => 20,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product1->id,
                'productPriceId' => $productPrice1->id,
                'quantity' => 2,
            ],
            [
                'productId' => $product2->id,
                'productPriceId' => $productPrice2->id,
                'quantity' => 3,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it validates when event has null end date and product has end sale date', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDay(),
        'end_date' => null,
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addDays(30),
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});

test('it validates when product has both null start and end sale dates and uses event dates', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'start_sale_date' => null,
        'end_sale_date' => null,
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Regular',
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 1,
            ],
        ]
    );

    $this->service->validateOrder($orderData);

    expect(true)->toBeTrue();
});
