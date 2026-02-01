<?php

namespace Tests\Feature;

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $organizer = Organizer::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);
    $product = Product::factory()->create(['event_id' => $event->id]);

    $this->order = Order::create([
        'event_id' => $event->id,
        'status' => OrderStatus::PENDING,
        'total' => 100,
        'reference_id' => 'REF123',
        'subtotal' => 100,
        'taxes_total' => 0,
        'fees_total' => 0,
        'total_gross' => 100,
        'total_gross' => 100,
        'used_payment_gateway_snapshot' => 'none',
        'items_snapshot' => [],
        'taxes_snapshot' => [],
        'fees_snapshot' => [],
        'expires_at' => now()->addHour(),
    ]);
});

test('guest can quick register from checkout', function () {
    $response = post(route('orders.register', $this->order), [
        'email' => 'guest@example.com'
    ]);

    $response->assertRedirect();
    // flash_success return array, so session 'message' should exist.
    $response->assertSessionHas('message');

    // Verify User Created
    $this->assertDatabaseHas('users', ['email' => 'guest@example.com']);

    // Verify Order Linked
    $user = User::where('email', 'guest@example.com')->first();
    $this->assertEquals($user->id, $this->order->refresh()->user_id);

    // Verify Logged In
    $this->assertAuthenticatedAs($user);
});

test('guest can access checkout page', function () {
    $response = $this->get(route('orders.checkout', $this->order));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('orders/Checkout'));
});

test('logged in user can link order', function () {
    $user = User::factory()->create(['email' => 'guest@example.com']);

    actingAs($user)
        ->post(route('orders.register', $this->order))
        ->assertRedirect();

    $this->assertEquals($user->id, $this->order->refresh()->user_id);
});

test('free order redirects to checkout and can be completed', function () {
    // Create a free order
    $this->order->update(['total_gross' => 0]);

    // Attempting to register/confirm as guest
    // In real flow: 1. Create Order -> Pending -> Redirect Checkout
    // 2. Checkout -> Click Confirm/Pay -> POST payment-intent

    // Verify it is PENDING
    $this->assertEquals(OrderStatus::PENDING, $this->order->status);

    // Simulate clicking "Confirm" (which hits payment-intent endpoint)
    // For free orders, CreatePaymentIntent now handles it.

    $response = $this->post(route('orders.payment-intent', $this->order), []);

    $response->assertRedirect(route('orders.show', $this->order));

    $this->assertEquals(OrderStatus::COMPLETED, $this->order->refresh()->status);
});

test('logging in with return_url redirects back', function () {
    $user = User::factory()->create([
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
    $returnUrl = route('orders.checkout', $this->order);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'return_url' => $returnUrl,
    ]);

    $response->assertRedirect($returnUrl);
});
