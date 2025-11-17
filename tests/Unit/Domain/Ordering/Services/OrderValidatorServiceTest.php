<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Services;

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use Domain\Ordering\Services\OrderValidatorService;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderValidatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderValidatorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderValidatorService();
    }

    /** @test */
    public function it_throws_exception_when_event_is_not_published(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Event is not published.');

        $event = Event::factory()->create(['status' => EventStatus::DRAFT]);

        $orderData = new CreateOrderData($event->id, []);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_passes_validation_for_published_event_with_no_products(): void
    {
        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);

        $orderData = new CreateOrderData($event->id, []);

        $this->service->validateOrder($orderData);

        $this->assertTrue(true); // No exception thrown
    }

    /** @test */
    public function it_throws_exception_when_product_does_not_exist_in_event(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Event product doesn\'t exist');

        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $nonExistentProductId = 999999;

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($nonExistentProductId, 1, 1),
        ]);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_throws_exception_when_selected_price_does_not_exist(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Selected price doesn\'t exist');

        $event = Event::factory()->create(['status' => EventStatus::PUBLISHED]);
        $product = Product::factory()->create(['event_id' => $event->id]);

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($product->id, 999999, 1),
        ]);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_throws_exception_when_product_sales_are_not_available(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Product sales are not available.');

        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(20),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->addDays(5),
            'end_sale_date' => now()->addDays(8),
        ]);

        $price = ProductPrice::factory()->create(['product_id' => $product->id]);

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($product->id, $price->id, 1),
        ]);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_throws_exception_when_price_sales_are_not_available(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Product with this price sales are not available');

        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(20),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => now()->addDays(10),
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'start_sale_date' => now()->addDays(5),
            'end_sale_date' => now()->addDays(8),
        ]);

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($product->id, $price->id, 1),
        ]);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_throws_exception_when_insufficient_stock(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Product is not available');

        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(20),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => now()->addDays(10),
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => now()->addDays(10),
        ]);

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($product->id, $price->id, 10), // Requesting 10 but only 5 available
        ]);

        $this->service->validateOrder($orderData);
    }

    /** @test */
    public function it_passes_validation_with_valid_order_data(): void
    {
        $event = Event::factory()->create([
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(20),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => now()->addDays(10),
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => now()->addDays(10),
        ]);

        $orderData = new CreateOrderData($event->id, [
            new CreateOrderProductData($product->id, $price->id, 5),
        ]);

        $this->service->validateOrder($orderData);

        $this->assertTrue(true); // No exception thrown
    }
}