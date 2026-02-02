<?php

use Domain\Ordering\Events\OrderCreated;
use Domain\Ordering\Models\Order;
use Domain\Promoters\Listeners\CreateCommissionForOrder;
use Domain\Promoters\Models\Promoter;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates pending commission for valid referral code and enabled promoter event', function () {
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $event = \Domain\EventManagement\Models\Event::factory()->create(['organizer_id' => $organizer->id]);
    $promoter = Promoter::factory()
        ->withOrganizer($organizer, [
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'enabled' => true,
        ])
        ->create([
            'referral_code' => 'TESTCODE',
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
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $event = \Domain\EventManagement\Models\Event::factory()->create(['organizer_id' => $organizer->id]);
    $promoter = Promoter::factory()
        ->withOrganizer($organizer, [
            'commission_type' => 'fixed',
            'commission_value' => 5.50,
            'enabled' => true,
        ])
        ->create([
            'referral_code' => 'FIXEDCODE',
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

test('it does not create commission if from different organizer', function () {
    // Create promoter for Organizer A
    $organizerA = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $promoter = Promoter::factory()
        ->withOrganizer($organizerA)
        ->create([
            'referral_code' => 'TESTCODE',
        ]);

    // Create Event for Organizer B
    $organizerB = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $eventB = \Domain\EventManagement\Models\Event::factory()->create(['organizer_id' => $organizerB->id]);

    $order = Order::factory()->create([
        'event_id' => $eventB->id,
        'referral_code' => 'TESTCODE',
    ]);

    $listener = new CreateCommissionForOrder();
    $eventDispatched = new OrderCreated($order, 'TESTCODE');

    $listener->handle($eventDispatched);

    $this->assertDatabaseCount('commissions', 0);
});

test('it does not create commission if promoter is disabled', function () {
    $organizer = \Domain\OrganizerManagement\Models\Organizer::factory()->create();
    $event = \Domain\EventManagement\Models\Event::factory()->create(['organizer_id' => $organizer->id]);

    $promoter = Promoter::factory()
        ->withOrganizer($organizer, [
            'enabled' => false, // Disabled
            'commission_type' => 'fixed',
            'commission_value' => 10,
        ])
        ->create([
            'referral_code' => 'TESTCODE',
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
