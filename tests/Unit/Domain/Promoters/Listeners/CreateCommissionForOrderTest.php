<?php

use Domain\Ordering\Events\OrderCreated;
use Domain\Ordering\Models\Order;
use Domain\Promoters\Listeners\CreateCommissionForOrder;
use Domain\Promoters\Models\Promoter;
use Domain\Promoters\Models\PromoterEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates pending commission for valid referral code and enabled promoter event', function () {
    $promoter = Promoter::factory()->create(['referral_code' => 'TESTCODE']);
    $event = \Domain\EventManagement\Models\Event::factory()->create();

    $promoterEvent = PromoterEvent::create([
        'promoter_id' => $promoter->id,
        'event_id' => $event->id,
        'commission_type' => 'percentage',
        'commission_value' => 10,
        'enabled' => true,
    ]);

    $order = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
        'total_gross' => 100.00,
    ]);

    $listener = new CreateCommissionForOrder();
    $eventDispatched = new OrderCreated($order, 'TESTCODE');

    $listener->handle($eventDispatched);

    $this->assertDatabaseHas('commissions', [
        'promoter_id' => $promoter->id,
        'order_id' => $order->id,
        'event_id' => $event->id,
        'amount' => 10.00,
        'status' => 'pending',
    ]);
});

test('it calculates fixed commission correctly', function () {
    $promoter = Promoter::factory()->create(['referral_code' => 'FIXEDCODE']);
    $event = \Domain\EventManagement\Models\Event::factory()->create();

    $promoterEvent = PromoterEvent::create([
        'promoter_id' => $promoter->id,
        'event_id' => $event->id,
        'commission_type' => 'fixed',
        'commission_value' => 5.50,
        'enabled' => true,
    ]);

    $order = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'FIXEDCODE',
        'total_gross' => 100.00,
    ]);

    $listener = new CreateCommissionForOrder();
    $eventDispatched = new OrderCreated($order, 'FIXEDCODE');

    $listener->handle($eventDispatched);

    $this->assertDatabaseHas('commissions', [
        'amount' => 5.50,
    ]);
});

test('it does not create commission if no referral code', function () {
    $listener = new CreateCommissionForOrder();
    $order = Order::factory()->create();
    $eventDispatched = new OrderCreated($order, null);

    $listener->handle($eventDispatched);

    $this->assertDatabaseCount('commissions', 0);
});

test('it does not create commission if promoter not found', function () {
    $listener = new CreateCommissionForOrder();
    $order = Order::factory()->create();
    $eventDispatched = new OrderCreated($order, 'INVALIDCODE');

    $listener->handle($eventDispatched);

    $this->assertDatabaseCount('commissions', 0);
});

test('it does not create commission if promoter event not found or disabled', function () {
    $promoter = Promoter::factory()->create(['referral_code' => 'TESTCODE']);
    $event = \Domain\EventManagement\Models\Event::factory()->create();

    // Create disabled rule
    PromoterEvent::create([
        'promoter_id' => $promoter->id,
        'event_id' => $event->id,
        'commission_type' => 'fixed',
        'commission_value' => 10,
        'enabled' => false,
    ]);

    $order = Order::factory()->create([
        'event_id' => $event->id,
        'referral_code' => 'TESTCODE',
    ]);

    $listener = new CreateCommissionForOrder();
    $eventDispatched = new OrderCreated($order, 'TESTCODE');

    $listener->handle($eventDispatched);

    $this->assertDatabaseCount('commissions', 0);
});
