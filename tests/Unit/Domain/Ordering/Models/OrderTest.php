<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Models;

use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\EventManagement\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_order_items_relationship(): void
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $order->order_items()
        );
    }

    /** @test */
    public function it_can_create_order_with_all_fields(): void
    {
        $event = Event::factory()->create();
        
        $order = Order::create([
            'event_id' => $event->id,
            'total_before_additions' => '100.00',
            'total_gross' => '115.00',
            'status' => 'pending',
            'expires_at' => now()->addHours(2)->toDateTimeString(),
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($event->id, $order->event_id);
        $this->assertEquals('100.00', $order->total_before_additions);
        $this->assertEquals('115.00', $order->total_gross);
        $this->assertEquals('pending', $order->status);
    }

    /** @test */
    public function it_allows_mass_assignment_with_guarded_empty(): void
    {
        $data = [
            'event_id' => 1,
            'total_before_additions' => '50.00',
            'total_gross' => '57.50',
            'status' => 'completed',
        ];

        $order = new Order($data);

        $this->assertEquals(1, $order->event_id);
        $this->assertEquals('50.00', $order->total_before_additions);
        $this->assertEquals('57.50', $order->total_gross);
        $this->assertEquals('completed', $order->status);
    }

    /** @test */
    public function it_can_have_multiple_order_items(): void
    {
        $order = Order::factory()->create();
        
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertEquals(3, $order->order_items()->count());
    }

    /** @test */
    public function it_stores_monetary_values_as_strings(): void
    {
        $order = Order::create([
            'event_id' => Event::factory()->create()->id,
            'total_before_additions' => '1234.56',
            'total_gross' => '1400.00',
            'status' => 'pending',
        ]);

        $this->assertIsString($order->total_before_additions);
        $this->assertIsString($order->total_gross);
        $this->assertEquals('1234.56', $order->total_before_additions);
        $this->assertEquals('1400.00', $order->total_gross);
    }

    /** @test */
    public function it_has_timestamps(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }

    /** @test */
    public function it_can_have_different_statuses(): void
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'expired'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create(['status' => $status]);
            $this->assertEquals($status, $order->status);
        }
    }
}