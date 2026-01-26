<?php

use Domain\Ordering\Actions\CreateOrder;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates commission when using create order action', function () {
    // 1. Setup Promoter and Event Rule
    $promoter = Promoter::factory()->create(['referral_code' => 'PROMO123']);
    $event = \Domain\EventManagement\Models\Event::factory()->create();

    $promoterEvent = PromoterEvent::create([
        'promoter_id' => $promoter->id,
        'event_id' => $event->id,
        'commission_type' => 'percentage',
        'commission_value' => 10,
        'enabled' => true,
    ]);

    // 2. Setup Product and Price
    $product = \Domain\ProductCatalog\Models\Product::factory()->create([
        'event_id' => $event->id,
        'hide_before_sale_start_date' => false,
        'hide_after_sale_end_date' => false,
        'start_sale_date' => now()->subDay(),
        'end_sale_date' => now()->addDay(),
    ]);
    $price = \Domain\ProductCatalog\Models\ProductPrice::factory()->create([
        'product_id' => $product->id,
        'price' => 100.00,
    ]);

    // Attach price to event setup if needed, but linking via product_id is usually enough for price calculation service mock? 
    // Actually, CreateOrderAction uses real services.
    // Ensure product is linked to event. it is via factory.

    // 3. Create Order Data
    // We need to match the structure expected by CreateOrderData
    // CreateOrderData uses array of items, but prepareOrderItems expects 'productId', 'productPriceId', etc.
    $orderData = new CreateOrderData(
        eventId: $event->id,
        items: [
            [
                'productId' => $product->id,
                'productPriceId' => $price->id,
                'quantity' => 1
            ]
        ],
        referral_code: 'PROMO123'
    );

    // 4. Run Action
    $action = app(CreateOrder::class);
    $order = $action->handle($orderData);

    // 5. Assertions
    $this->assertDatabaseHas('commissions', [
        'order_id' => $order->id,
        'promoter_id' => $promoter->id,
        'amount' => 10.00,
        'status' => 'pending',
    ]);
});
