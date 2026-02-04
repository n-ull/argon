<?php

namespace Tests\Feature;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Services\OrderService;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Combo;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComboOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_order_combo_and_deduct_stock()
    {
        // 1. Setup Event and Products
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);

        $product1 = Product::factory()->create([
            'event_id' => $event->id,
            'product_type' => ProductType::GENERAL, // Typo in Enum? Checking ProductType
            'name' => 'Product 1'
        ]);
        $price1 = ProductPrice::factory()->create([
            'product_id' => $product1->id,
            'stock' => 10,
            'quantity_sold' => 0,
            'price' => 100
        ]);

        $product2 = Product::factory()->create([
            'event_id' => $event->id,
            'product_type' => ProductType::GENERAL,
            'name' => 'Product 2'
        ]);
        $price2 = ProductPrice::factory()->create([
            'product_id' => $product2->id,
            'stock' => 20,
            'quantity_sold' => 0,
            'price' => 50
        ]);

        // 2. Create Combo: 2 of Product 1, 1 of Product 2. Total Value: 250. Combo Price: 200.
        $combo = Combo::create([
            'event_id' => $event->id,
            'name' => 'Super Combo',
            'price' => 200,
            'is_active' => true,
        ]);

        $combo->items()->create([
            'product_price_id' => $price1->id,
            'quantity' => 2
        ]);

        $combo->items()->create([
            'product_price_id' => $price2->id,
            'quantity' => 1
        ]);

        // 3. Create Order for 1 Combo
        $orderService = app(OrderService::class);
        $orderData = new CreateOrderData(
            eventId: $event->id,
            items: [
                ['comboId' => $combo->id, 'quantity' => 1]
            ],
            userId: null
        );

        $order = $orderService->createPendingOrder($orderData);

        // 4. Assertions
        $this->assertEquals(200, $order->subtotal, 'Order subtotal should match combo price');
        $this->assertCount(1, $order->orderItems);
        $this->assertEquals($combo->id, $order->orderItems->first()->combo_id);

        // Check Stock Deduction
        // Product 1: Start 10, Sold 0. Combo (qty 1) needs 2. Expected Sold: 2.
        $this->assertEquals(2, $price1->fresh()->quantity_sold, 'Product 1 stock deduction incorrect');

        // Product 2: Start 20, Sold 0. Combo (qty 1) needs 1. Expected Sold: 1.
        $this->assertEquals(1, $price2->fresh()->quantity_sold, 'Product 2 stock deduction incorrect');
    }

    public function test_cannot_order_combo_if_out_of_stock()
    {
        // 1. Setup
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);

        $product = Product::factory()->create(['event_id' => $event->id]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'quantity_sold' => 4, // Only 1 left
            'price' => 100
        ]);

        $combo = Combo::create([
            'event_id' => $event->id,
            'name' => 'Stock Check Combo',
            'price' => 150,
        ]);

        // Combo needs 2 of the product
        $combo->items()->create([
            'product_price_id' => $price->id,
            'quantity' => 2
        ]);

        // 2. Attempt Order
        $orderService = app(OrderService::class);
        $orderData = new CreateOrderData(
            eventId: $event->id,
            items: [
                ['comboId' => $combo->id, 'quantity' => 1]
            ]
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Not enough stock');

        $orderService->createPendingOrder($orderData);
    }
    public function test_generates_tickets_and_counts_combo_sales()
    {
        // 1. Setup - Product 1 is a TICKET
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);

        $ticketProduct = Product::factory()->create([
            'event_id' => $event->id,
            'product_type' => ProductType::TICKET,
            'ticket_type' => \Domain\Ticketing\Enums\TicketType::STATIC ,
            'name' => 'Ticket Product'
        ]);
        $ticketPrice = ProductPrice::factory()->create([
            'product_id' => $ticketProduct->id,
            'stock' => 100,
            'price' => 50
        ]);

        $combo = Combo::create([
            'event_id' => $event->id,
            'name' => 'Ticket Combo',
            'price' => 40,
        ]);
        $combo->items()->create(['product_price_id' => $ticketPrice->id, 'quantity' => 2]); // 2 tickets per combo

        // 2. Create Order
        $orderService = app(OrderService::class);
        $orderData = new CreateOrderData(
            eventId: $event->id,
            items: [['comboId' => $combo->id, 'quantity' => 1]]
        );
        $order = $orderService->createPendingOrder($orderData);

        // Verify Combo Quantity Sold Incremented
        $this->assertEquals(1, $combo->fresh()->quantity_sold, 'Combo quantity sold should be 1');

        // 3. Complete Order to trigger Ticket Generation
        // Trigger job manually or mock? GenerateTickets is a Job. Dispatching it.
        $order->update(['status' => \Domain\Ordering\Enums\OrderStatus::COMPLETED]);
        (new \Domain\Ticketing\Jobs\GenerateTickets($order->id))->handle();

        // 4. Verify Tickets
        // Combo has 2 tickets * 1 qty = 2 tickets total
        $tickets = \Domain\Ticketing\Models\Ticket::where('order_id', $order->id)->get();
        $this->assertCount(2, $tickets, 'Should generate 2 tickets for the combo');
        $this->assertEquals($ticketProduct->id, $tickets->first()->product_id);
    }
}
