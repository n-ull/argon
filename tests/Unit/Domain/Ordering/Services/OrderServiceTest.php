<?php

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Services\OrderService;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\Ordering\Services\PriceCalculationService;
use Domain\Ordering\Services\ReferenceIdService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->orderValidatorService = Mockery::mock(OrderValidatorService::class);
    $this->priceCalculationService = new PriceCalculationService;
    $this->referenceIdService = Mockery::mock(ReferenceIdService::class);

    $this->databaseManager = app(\Illuminate\Database\DatabaseManager::class);

    $this->service = new OrderService(
        $this->orderValidatorService,
        $this->priceCalculationService,
        $this->referenceIdService,
        $this->databaseManager
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
            [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => 2,
            ],
        ],
        userId: 1,
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
    expect($order->status)->toBe(OrderStatus::PENDING);
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

test('it increments quantity_sold on ProductPrice when creating an order', function () {
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $product = Product::factory()->create(['event_id' => $event->id]);
    $productPrice = ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100.00,
        'label' => 'Standard',
        'stock' => 10,
        'quantity_sold' => 0,
        'is_hidden' => false,
        'sort_order' => 1,
    ]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [['productId' => $product->id, 'productPriceId' => $productPrice->id, 'quantity' => 3]]
    );

    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-1');

    $this->service->createPendingOrder($orderData);

    $productPrice->refresh();
    expect($productPrice->quantity_sold)->toBe(3);
});

test('it creates order with multiple products and stores all snapshots', function () {
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $product1 = Product::factory()->create(['event_id' => $event->id]);
    $price1 = ProductPrice::factory()->create(['product_id' => $product1->id, 'price' => 50.0]);
    $product2 = Product::factory()->create(['event_id' => $event->id]);
    $price2 = ProductPrice::factory()->create(['product_id' => $product2->id, 'price' => 75.0]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            ['productId' => $product1->id, 'productPriceId' => $price1->id, 'quantity' => 1],
            ['productId' => $product2->id, 'productPriceId' => $price2->id, 'quantity' => 2],
        ]
    );

    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-1');

    $order = $this->service->createPendingOrder($orderData);

    expect((float) $order->subtotal)->toBe(200.0); // 50 + (75*2)
    expect($order->orderItems)->toHaveCount(2);
    expect($order->items_snapshot)->toHaveCount(2);
});

test('it stores taxes and fees snapshots when applicable', function () {
    $tax = \Domain\EventManagement\Models\TaxAndFee::factory()->state([
        'type' => \Domain\EventManagement\Enums\TaxFeeType::TAX,
        'value' => 10,
        'is_active' => true,
        'calculation_type' => \Domain\EventManagement\Enums\CalculationType::PERCENTAGE
    ])->create();

    $fee = \Domain\EventManagement\Models\TaxAndFee::factory()->state([
        'type' => \Domain\EventManagement\Enums\TaxFeeType::FEE,
        'value' => 5,
        'is_active' => true,
        'calculation_type' => \Domain\EventManagement\Enums\CalculationType::FIXED
    ])->create();

    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $event->taxesAndFees()->attach($tax, ['sort_order' => 1]);
    $event->taxesAndFees()->attach($fee, ['sort_order' => 2]);

    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100.0]);

    $orderData = new CreateOrderData(eventId: $event->id, items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]]);

    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-TAX');

    $order = $this->service->createPendingOrder($orderData);

    expect((float) $order->subtotal)->toBe(100.0);
    expect((float) $order->taxes_total)->toBe(10.0); // 10% of 100
    expect((float) $order->fees_total)->toBe(5.0); // Fixed 5
    expect((float) $order->total_gross)->toBe(115.0);
    expect($order->taxes_snapshot)->toHaveCount(1);
    expect($order->fees_snapshot)->toHaveCount(1);
});

test('it throws exception when event does not exist', function () {
    $orderData = new CreateOrderData(eventId: 999, items: []);
    $this->service->createPendingOrder($orderData);
})->throws(\DomainException::class, "Event doesn't exist.");

test('it sets order expiration to 15 minutes from creation', function () {
    Carbon\Carbon::setTestNow(now());
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100.0]);

    $orderData = new CreateOrderData(eventId: $event->id, items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]]);
    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-EXP');

    $order = $this->service->createPendingOrder($orderData);

    expect($order->expires_at->toDateTimeString())->toBe(now()->addMinutes(15)->toDateTimeString());
    Carbon\Carbon::setTestNow();
});

test('it dispatches OrderCreated event after order creation', function () {
    Illuminate\Support\Facades\Event::fake();
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100.0]);

    $orderData = new CreateOrderData(eventId: $event->id, items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]]);
    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-EVENT');

    $order = $this->service->createPendingOrder($orderData);

    Illuminate\Support\Facades\Event::assertDispatched(\Domain\Ordering\Events\OrderCreated::class, function ($event) use ($order) {
        return $event->order->id === $order->id;
    });
});

test('it stores snapshots after order completion', function () {
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()
        ->has(\Domain\OrganizerManagement\Models\OrganizerSettings::factory()->state(['raise_money_method' => 'internal']), 'settings')
        ->create();
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED, 'organizer_id' => $organizer->id]);
    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100.0]);

    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]],
        gateway: 'mercadopago'
    );
    $this->orderValidatorService->shouldReceive('validateOrder')->once();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-SNAP');

    $order = $this->service->createPendingOrder($orderData);

    expect($order->organizer_raise_method_snapshot)->toBe('internal');
    expect($order->used_payment_gateway_snapshot)->toBe('mercadopago');
});

test('it handles gateway-specific fees correctly', function () {
    $taxFee = \Domain\EventManagement\Models\TaxAndFee::factory()->create([
        'type' => \Domain\EventManagement\Enums\TaxFeeType::FEE,
        'value' => 5,
        'is_active' => true,
        'calculation_type' => \Domain\EventManagement\Enums\CalculationType::FIXED,
        'applicable_gateways' => ['mercadopago']
    ]);
    $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
    $event->taxesAndFees()->attach($taxFee);

    $product = Product::factory()->create(['event_id' => $event->id]);
    $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100.0]);

    $productPrice = $price;

    $this->orderValidatorService->shouldReceive('validateOrder')->andReturnNull();
    $this->referenceIdService->shouldReceive('create')->andReturn('REF-GW-1', 'REF-GW-2');

    // Case 1: Gateway matches
    $orderData1 = new CreateOrderData(
        eventId: $event->id,
        items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]],
        gateway: 'mercadopago'
    );
    $order1 = $this->service->createPendingOrder($orderData1);
    expect((float) $order1->fees_total)->toBe(5.0);

    // Case 2: Gateway does not match
    $orderData2 = new CreateOrderData(
        eventId: $event->id,
        items: [['productId' => $product->id, 'productPriceId' => $price->id, 'quantity' => 1]],
        gateway: 'other'
    );
    $order2 = $this->service->createPendingOrder($orderData2);
    expect((float) $order2->fees_total)->toBe(0.0);
});

test('it completes a pending order via ID', function () {
    Illuminate\Support\Facades\Event::fake();
    $order = \Domain\Ordering\Models\Order::factory()->create(['status' => OrderStatus::PENDING]);

    $completedOrder = $this->service->completePendingOrder(orderId: $order->id);

    expect($completedOrder->status)->toBe(OrderStatus::COMPLETED);
    Illuminate\Support\Facades\Event::assertDispatched(\Domain\Ordering\Events\OrderCompleted::class);
});

test('it completes a pending order via Reference ID', function () {
    $order = \Domain\Ordering\Models\Order::factory()->create(['status' => OrderStatus::PENDING, 'reference_id' => 'REF-123']);

    $completedOrder = $this->service->completePendingOrder(referenceId: 'REF-123');

    expect($completedOrder->status)->toBe(OrderStatus::COMPLETED);
});

test('it throws exception when completing already completed order', function () {
    $order = \Domain\Ordering\Models\Order::factory()->create(['status' => OrderStatus::COMPLETED, 'reference_id' => 'REF-DONE']);
    $this->service->completePendingOrder(referenceId: 'REF-DONE');
})->throws(\Domain\Ordering\Exceptions\OrderAlreadyCompletedException::class);

test('it throws exception when completing non-existent order', function () {
    $this->service->completePendingOrder(orderId: 9999);
})->throws(\Domain\Ordering\Exceptions\OrderNotFoundException::class);
