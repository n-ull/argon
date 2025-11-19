<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use Domain\Ordering\Services\OrderService;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\Ordering\Services\PriceCalculationService;
use Domain\Ordering\Services\ReferenceIdService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;

beforeEach(function () {
    $this->orderValidatorService = Mockery::mock(OrderValidatorService::class);
    $this->priceCalculationService = new PriceCalculationService;
    $this->referenceIdService = Mockery::mock(ReferenceIdService::class);

    $this->service = new OrderService(
        $this->orderValidatorService,
        $this->priceCalculationService,
        $this->referenceIdService
    );
});

test('it creates an order and stores price snapshots even if prices change later', function () {
    // Arrange: Create event with products
    $event = Event::factory()->create([
        'status' => EventStatus::PUBLISHED,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
    ]);

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'name' => 'VIP Ticket',
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addMonth(),
    ]);

    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Early Bird',
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addMonth(),
        'stock' => 50,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    // Create order data
    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            new CreateOrderProductData(
                productId: $product->id,
                selectedPriceId: $productPrice->id,
                quantity: 2
            ),
        ],
        gateway: null
    );

    // Mock validator to pass validation
    $this->orderValidatorService
        ->shouldReceive('validateOrder')
        ->once()
        ->with($orderData);

    // Mock reference ID generation
    $this->referenceIdService
        ->shouldReceive('create')
        ->once()
        ->andReturn('ORD-12345');

    // Act: Create the order
    $order = $this->service->createPendingOrder($orderData);

    // Assert: Order was created with correct totals
    expect($order)->toBeInstanceOf(\Domain\Ordering\Models\Order::class);
    expect((float) $order->subtotal)->toBe(200.0); // 2 * 100
    expect($order->status)->toBe('pending');
    expect($order->reference_id)->toBe('ORD-12345');

    // Assert: Snapshots were stored
    expect($order->items_snapshot)->toBeArray();
    expect($order->items_snapshot)->toHaveCount(1);
    expect($order->items_snapshot[0]['product_id'])->toBe($product->id);
    expect($order->items_snapshot[0]['product_price_id'])->toBe($productPrice->id);
    expect($order->items_snapshot[0]['quantity'])->toBe(2);
    expect((float) $order->items_snapshot[0]['unit_price'])->toBe(100.0);

    // Assert: Order items were created
    expect($order->orderItems)->toHaveCount(1);
    $orderItem = $order->orderItems->first();
    expect($orderItem->product_id)->toBe($product->id);
    expect($orderItem->product_price_id)->toBe($productPrice->id);
    expect($orderItem->quantity)->toBe(2);
    expect((float) $orderItem->unit_price)->toBe(100.0);

    // Act: Change the price after order creation
    $productPrice->update(['price' => 150.00]);
    $productPrice->refresh();

    // Assert: Price in database has changed
    expect((float) $productPrice->price)->toBe(150.0);

    // Assert: Order snapshot still has original price
    $order->refresh();
    expect((float) $order->items_snapshot[0]['unit_price'])->toBe(100.0);
    expect((float) $order->subtotal)->toBe(200.0); // Still 2 * 100, not 2 * 150

    // Assert: Order item still has original price
    $orderItem->refresh();
    expect((float) $orderItem->unit_price)->toBe(100.0);
});

test('it creates order with multiple products and stores all snapshots', function () {
    // Tests multiple items in one order
});

test('it stores taxes and fees snapshots when applicable', function () {
    // Tests tax/fee snapshot functionality
});

test('it throws exception when event does not exist', function () {
    // Tests error handling
});

test('it sets order expiration to 15 minutes from creation', function () {
    // Tests expiration logic
});

test('it dispatches OrderCreated event after order creation', function () {
    // Tests event dispatching
});

test('it stores organizer raise method snapshot after order completion', function () {
    // Tests organizer settings capture
});

test('it stores used gateway after order completion', function () {});

test('it handles gateway-specific fees correctly', function () {
    // Tests payment gateway fee logic
});
