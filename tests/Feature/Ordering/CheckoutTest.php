<?php

namespace Tests\Feature\Ordering;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Jobs\ExpireOrder;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_cancel_pending_order()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('orders.cancel', $order->id));

        $response->assertRedirect(route('events.show', $event->slug));
        $this->assertEquals(OrderStatus::CANCELLED, $order->fresh()->status);
    }

    public function test_expiration_job_is_dispatched()
    {
        Bus::fake();

        $user = User::factory()->create();
        $event = Event::factory()->create(['start_date' => now()->subDays(1)]);
        $product = Product::factory()->create(['event_id' => $event->id]);
        $price = ProductPrice::factory()->for($product)->create(['price' => 100]);

        $this->actingAs($user);

        $this->post(route('orders.store'), [
            'eventId' => $event->id,
            'items' => [
                [
                    'productId' => $product->id,
                    'productPriceId' => $price->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        Bus::assertDispatched(ExpireOrder::class);
    }
}
