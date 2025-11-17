<?php

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('OrderItem Model', function () {
    test('can create an order item', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $product = Product::factory()->create(['event_id' => $event->id]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        expect($orderItem)->toBeInstanceOf(OrderItem::class)
            ->and($orderItem->order_id)->toBe($order->id)
            ->and($orderItem->product_id)->toBe($product->id)
            ->and($orderItem->quantity)->toBe(5);
    });

    test('belongs to an order', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
        ]);

        expect($orderItem->order)->toBeInstanceOf(Order::class)
            ->and($orderItem->order->id)->toBe($order->id);
    });

    test('belongs to a product', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $product = Product::factory()->create(['event_id' => $event->id]);
        
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        expect($orderItem->product)->toBeInstanceOf(Product::class)
            ->and($orderItem->product->id)->toBe($product->id);
    });

    test('has no guarded attributes', function () {
        $orderItem = new OrderItem();

        expect($orderItem->getGuarded())->toBe(['*']);
    });

    test('can update quantity', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'quantity' => 2,
        ]);

        $orderItem->update(['quantity' => 10]);

        expect($orderItem->fresh()->quantity)->toBe(10);
    });

    test('quantity can be null', function () {
        $event = Event::factory()->create();
        $order = Order::create(['event_id' => $event->id]);
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => 1,
            'quantity' => null,
        ]);

        expect($orderItem->quantity)->toBeNull();
    });
});