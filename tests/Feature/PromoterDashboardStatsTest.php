<?php

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\Product;
use Domain\Promoters\Models\Promoter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

uses(RefreshDatabase::class);

test('it can get my promoter sales stats for an event', function () {
    $user = User::factory()->create();
    $promoter = Promoter::factory()->create([
        'user_id' => $user->id,
        'referral_code' => 'TESTCODE'
    ]);

    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create(['owner_id' => $user->id]);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    // Products
    $productA = Product::factory()->create(['event_id' => $event->id, 'name' => 'Product A']);
    $productB = Product::factory()->create(['event_id' => $event->id, 'name' => 'Product B']);

    // Order 1: 2x Product A (Completed)
    $order1 = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
        'status' => OrderStatus::COMPLETED,
    ]);
    OrderItem::factory()->create(['order_id' => $order1->id, 'product_id' => $productA->id, 'quantity' => 2]);

    // Order 2: 1x Product B (Completed)
    $order2 = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
        'status' => OrderStatus::COMPLETED,
    ]);
    OrderItem::factory()->create(['order_id' => $order2->id, 'product_id' => $productB->id, 'quantity' => 1]);

    // Order 3: 1x Product A (Completed)
    $order3 = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
        'status' => OrderStatus::COMPLETED,
    ]);
    OrderItem::factory()->create(['order_id' => $order3->id, 'product_id' => $productA->id, 'quantity' => 1]);

    // Order 4: 1x Product A (Pending - should be ignored)
    $order4 = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
        'status' => OrderStatus::PENDING,
    ]);
    OrderItem::factory()->create(['order_id' => $order4->id, 'product_id' => $productA->id, 'quantity' => 5]);

    // Order 5: NO Referral Code (Should be ignored)
    $order5 = Order::factory()->create([
        'event_id' => $event->id,
        'status' => OrderStatus::COMPLETED,
    ]);
    OrderItem::factory()->create(['order_id' => $order5->id, 'product_id' => $productA->id, 'quantity' => 10]);


    $response = $this->actingAs($user)
        ->get(route('promoters.events.stats', ['event' => $event->id]));

    $response->assertStatus(200);

    $data = $response->json();

    // Product A: 2 + 1 = 3
    // Product B: 1 = 1

    expect($data)->toHaveCount(2);

    $statsA = collect($data)->firstWhere('product_id', $productA->id);
    expect($statsA['quantity'])->toBe(3);

    $statsB = collect($data)->firstWhere('product_id', $productB->id);
    expect($statsB['quantity'])->toBe(1);
});

test('it cannot get stats if not a promoter', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('promoters.events.stats', ['event' => $event->id]));

    $response->assertStatus(403);
});
