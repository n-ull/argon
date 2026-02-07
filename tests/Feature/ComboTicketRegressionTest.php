<?php

namespace Tests\Feature;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Combo;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Domain\Ticketing\Enums\TicketType;
use Domain\Ticketing\Jobs\GenerateTickets;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComboTicketRegressionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_reproduce_combo_item_falling_through_to_single_ticket_generation()
    {
        // 1. Create a Product that is a TICKET type
        $ticketProduct = Product::factory()->create([
            'product_type' => ProductType::TICKET,
            'ticket_type' => TicketType::STATIC ,
        ]);

        $ticketPrice = ProductPrice::factory()->create([
            'product_id' => $ticketProduct->id,
        ]);

        // 2. Create an Order
        $order = Order::factory()->create([
            'status' => \Domain\Ordering\Enums\OrderStatus::COMPLETED,
        ]);

        // 3. Create a Combo and soft-delete it to simulate missing combo relation
        $combo = Combo::create([
            'event_id' => $ticketProduct->event_id ?? 1,
            'name' => 'Deleted Combo',
            'price' => 10
        ]);
        $combo->delete();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $ticketProduct->id,
            'product_price_id' => $ticketPrice->id,
            'combo_id' => $combo->id,
            'quantity' => 1,
        ]);

        // 4. Run the Job
        // We catch exception if it throws, or check if ticket is created
        try {
            (new GenerateTickets($order->id))->handle();
        } catch (\Throwable $e) {
            // If it crashes, that's a reproduction!
            // But we also want to catch the case where it simply generates WRONG ticket.
            $this->fail("Job crashed: ".$e->getMessage());
        }

        // 5. Assert that a Ticket was created for this item
        // The bug is that a ticket IS created for the wrapper item.
        // If we fix the bug, NO ticket should be created for this item (since it's a combo item with broken combo).
        // Or if it's a valid combo, the tickets should come from combo items, NOT the wrapper.

        $tickets = Ticket::where('order_id', $order->id)->get();

        // We EXPECT this to be > 0 if the bug exists.
        // Only assert failure if 0 tickets (meaning bug NOT reproduced?)
        // Wait, if bug is "it generates ticket", then > 0 means reproduced.

        $this->assertEquals(0, $tickets->count(), "Fix verified: No tickets generated for combo wrapper.");
    }
}
