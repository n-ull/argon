<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Models;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_an_order(): void
    {
        $order = Order::factory()->create();
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $orderItem->order);
        $this->assertEquals($order->id, $orderItem->order->id);
    }

    /** @test */
    public function it_belongs_to_a_product(): void
    {
        $product = Product::factory()->create();
        $orderItem = OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $orderItem->product);
        $this->assertEquals($product->id, $orderItem->product->id);
    }

    /** @test */
    public function it_can_have_a_quantity(): void
    {
        $orderItem = OrderItem::factory()->create(['quantity' => 5]);

        $this->assertEquals(5, $orderItem->quantity);
    }

    /** @test */
    public function it_allows_null_quantity(): void
    {
        $orderItem = OrderItem::factory()->create(['quantity' => null]);

        $this->assertNull($orderItem->quantity);
    }

    /** @test */
    public function it_allows_mass_assignment_with_guarded_empty(): void
    {
        $data = [
            'order_id' => Order::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'quantity' => 3,
        ];

        $orderItem = new OrderItem($data);

        $this->assertEquals($data['order_id'], $orderItem->order_id);
        $this->assertEquals($data['product_id'], $orderItem->product_id);
        $this->assertEquals(3, $orderItem->quantity);
    }

    /** @test */
    public function it_has_timestamps(): void
    {
        $orderItem = OrderItem::factory()->create();

        $this->assertNotNull($orderItem->created_at);
        $this->assertNotNull($orderItem->updated_at);
    }

    /** @test */
    public function multiple_order_items_can_belong_to_same_order(): void
    {
        $order = Order::factory()->create();

        $item1 = OrderItem::factory()->create(['order_id' => $order->id]);
        $item2 = OrderItem::factory()->create(['order_id' => $order->id]);
        $item3 = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertEquals(3, $order->order_items()->count());
        $this->assertTrue($order->order_items->contains($item1));
        $this->assertTrue($order->order_items->contains($item2));
        $this->assertTrue($order->order_items->contains($item3));
    }

    /** @test */
    public function it_can_store_large_quantities(): void
    {
        $orderItem = OrderItem::factory()->create(['quantity' => 999999]);

        $this->assertEquals(999999, $orderItem->quantity);
    }
}
