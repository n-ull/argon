<?php

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Order Model', function () {
    test('can create an order', function () {
        $event = Event::factory()->create();
        
        $order = Order::create([
            'event_id' => $event->id,
            'total_before_additions' => '100.00',
            'total_gross' => '120.00',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        expect($order)->toBeInstanceOf(Order::class)
            ->and($order->event_id)->toBe($event->id)
            ->and($order->total_before_additions)->toBe('100.00')
            ->and($order->total_gross)->toBe('120.00')
            ->and($order->status)->toBe('pending');
    });

    test('has many order items', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        
        $item1 = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'quantity' => 2,
        ]);
        
        $item2 = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 2,
            'quantity' => 3,
        ]);

        $order->load('order_items');

        expect($order->order_items)->toHaveCount(2)
            ->and($order->order_items->pluck('id')->toArray())->toContain($item1->id, $item2->id);
    });

    test('has no guarded attributes', function () {
        $order = new Order();

        expect($order->getGuarded())->toBe(['*']);
    });

    test('can update order status', function () {
        $event = Event::factory()->create();
        $order = Order::create([
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'completed']);

        expect($order->fresh()->status)->toBe('completed');
    });

    test('can update order totals', function () {
        $event = Event::factory()->create();
        $order = Order::create([
            'event_id' => $event->id,
            'total_before_additions' => '100.00',
            'total_gross' => '120.00',
        ]);

        $order->update([
            'total_before_additions' => '150.00',
            'total_gross' => '180.00',
        ]);

        expect($order->fresh()->total_before_additions)->toBe('150.00')
            ->and($order->fresh()->total_gross)->toBe('180.00');
    });
});

describe('Order Relationships', function () {
    test('order items belong to order', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
        ]);

        expect($item->order)->toBeInstanceOf(Order::class)
            ->and($item->order->id)->toBe($order->id);
    });
});