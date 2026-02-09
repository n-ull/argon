<?php

namespace Tests\Feature\Ordering;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GuestOrderIpSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_ip_is_cached_when_creating_order()
    {
        // Setup event and product
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $product = Product::factory()->create(['event_id' => $event->id]);
        $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100, 'stock' => 10]);

        // Create order as guest (not authenticated)
        $response = $this->post(route('orders.store'), [
            'eventId' => $event->id,
            'items' => [
                [
                    'productId' => $product->id,
                    'productPriceId' => $price->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();

        // Get the created order
        $order = Order::latest()->first();

        // Assert IP was cached with correct key
        $this->assertNotNull(Cache::get("guest_order_{$order->id}"));
        $this->assertEquals('127.0.0.1', Cache::get("guest_order_{$order->id}"));
    }

    public function test_authenticated_user_ip_is_not_cached()
    {
        // Setup event and product
        $user = User::factory()->create();
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $product = Product::factory()->create(['event_id' => $event->id]);
        $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100, 'stock' => 10]);

        // Create order as authenticated user
        $this->actingAs($user);

        $response = $this->post(route('orders.store'), [
            'eventId' => $event->id,
            'items' => [
                [
                    'productId' => $product->id,
                    'productPriceId' => $price->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertRedirect();

        // Get the created order
        $order = Order::latest()->first();

        // Assert IP was NOT cached for authenticated users
        $this->assertNull(Cache::get("guest_order_{$order->id}"));
    }

    public function test_guest_can_access_checkout_with_matching_ip()
    {
        // Create guest order
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null, // Guest order
            'event_id' => $event->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Cache the IP
        Cache::put("guest_order_{$order->id}", '127.0.0.1', 60 * 15);

        // Attempt to access checkout
        $response = $this->get(route('orders.checkout', $order->id));

        // Should be allowed
        $response->assertOk();
    }

    public function test_guest_cannot_access_checkout_with_different_ip()
    {
        // Create guest order
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null, // Guest order
            'event_id' => $event->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Cache a different IP
        Cache::put("guest_order_{$order->id}", '192.168.1.100', 60 * 15);

        // Attempt to access checkout from different IP (test IP is 127.0.0.1)
        $response = $this->get(route('orders.checkout', $order->id));

        // Should be redirected back to event
        $response->assertRedirect(route('events.show', $event->slug));
        $response->assertSessionHas('message');
    }

    public function test_guest_cannot_access_checkout_without_cached_ip()
    {
        // Create guest order
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null, // Guest order
            'event_id' => $event->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Don't cache any IP

        // Attempt to access checkout
        $response = $this->get(route('orders.checkout', $order->id));

        // Should be redirected back to event
        $response->assertRedirect(route('events.show', $event->slug));
        $response->assertSessionHas('message');
    }

    public function test_authenticated_user_can_access_their_order_checkout()
    {
        // Create user and their order
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Login as the user
        $this->actingAs($user);

        // Attempt to access checkout
        $response = $this->get(route('orders.checkout', $order->id));

        // Should be allowed
        $response->assertOk();
    }

    public function test_authenticated_user_cannot_access_another_users_order()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $event = Event::factory()->create();

        // Create order for user1
        $order = Order::factory()->create([
            'user_id' => $user1->id,
            'event_id' => $event->id,
            'status' => OrderStatus::PENDING,
        ]);

        // Login as user2
        $this->actingAs($user2);

        // Attempt to access user1's checkout
        $response = $this->get(route('orders.checkout', $order->id));

        // Should be redirected back to event
        $response->assertRedirect(route('events.show', $event->slug));
        $response->assertSessionHas('message');
    }

    public function test_ip_cache_expires_after_15_minutes()
    {
        // Setup event and product
        $event = Event::factory()->create(['start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $product = Product::factory()->create(['event_id' => $event->id]);
        $price = ProductPrice::factory()->create(['product_id' => $product->id, 'price' => 100, 'stock' => 10]);

        // Create order as guest
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

        $order = Order::latest()->first();

        // Verify cache exists
        $this->assertNotNull(Cache::get("guest_order_{$order->id}"));

        // Simulate time passing (travel 16 minutes into the future)
        $this->travel(16)->minutes();

        // Cache should be expired
        $this->assertNull(Cache::get("guest_order_{$order->id}"));
    }

    public function test_completed_order_redirects_to_order_show()
    {
        // Create completed order
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null,
            'event_id' => $event->id,
            'status' => OrderStatus::COMPLETED,
        ]);

        Cache::put("guest_order_{$order->id}", '127.0.0.1', 60 * 15);

        $response = $this->get(route('orders.checkout', $order->id));

        // Should redirect to order details, not checkout
        $response->assertRedirect(route('orders.show', $order->id));
    }

    public function test_expired_order_redirects_to_event_show()
    {
        // Create expired order
        $event = Event::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null,
            'event_id' => $event->id,
            'status' => OrderStatus::EXPIRED,
        ]);

        Cache::put("guest_order_{$order->id}", '127.0.0.1', 60 * 15);

        $response = $this->get(route('orders.checkout', $order->id));

        // Should redirect to event show
        $response->assertRedirect(route('events.show', $event->slug));
        $response->assertSessionHas('message');
    }
}
