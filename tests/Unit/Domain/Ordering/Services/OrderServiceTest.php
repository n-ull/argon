<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Services;

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Services\OrderService;
use Domain\Ordering\Services\OrderValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService(new OrderValidatorService);
    }

    /** @test */
    public function it_throws_exception_when_event_does_not_exist(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Event doesn't exist.");

        $orderData = new CreateOrderData(999999, []);

        $this->service->createPendingOrder($orderData);
    }

    /** @test */
    public function it_creates_order_for_existing_event(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);

        $orderData = new CreateOrderData($event->id, []);

        $order = $this->service->createPendingOrder($orderData);

        $this->assertNotNull($order);
        $this->assertEquals($event->id, $order->event_id);
    }

    /** @test */
    public function it_persists_order_to_database(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);

        $orderData = new CreateOrderData($event->id, []);

        $order = $this->service->createPendingOrder($orderData);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'event_id' => $event->id,
        ]);
    }
}
