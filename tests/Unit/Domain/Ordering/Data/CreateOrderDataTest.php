<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Data;

use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Data\CreateOrderProductData;
use PHPUnit\Framework\TestCase;

class CreateOrderDataTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_with_event_id_and_products(): void
    {
        $products = [
            new CreateOrderProductData(
                productId: 1,
                selectedPriceId: 10,
                quantity: 2
            ),
        ];

        $data = new CreateOrderData(
            eventId: 123,
            products: $products
        );

        $this->assertEquals(123, $data->eventId);
        $this->assertIsArray($data->products);
        $this->assertCount(1, $data->products);
        $this->assertInstanceOf(CreateOrderProductData::class, $data->products[0]);
    }

    /** @test */
    public function it_can_hold_multiple_products(): void
    {
        $products = [
            new CreateOrderProductData(1, 10, 2),
            new CreateOrderProductData(2, 20, 1),
            new CreateOrderProductData(3, 30, 5),
        ];

        $data = new CreateOrderData(456, $products);

        $this->assertCount(3, $data->products);
        $this->assertEquals(456, $data->eventId);
    }

    /** @test */
    public function it_can_have_empty_products_array(): void
    {
        $data = new CreateOrderData(789, []);

        $this->assertIsArray($data->products);
        $this->assertCount(0, $data->products);
    }

    /** @test */
    public function it_preserves_product_data_integrity(): void
    {
        $product = new CreateOrderProductData(
            productId: 42,
            selectedPriceId: 84,
            quantity: 10
        );

        $data = new CreateOrderData(100, [$product]);

        $this->assertEquals(42, $data->products[0]->productId);
        $this->assertEquals(84, $data->products[0]->selectedPriceId);
        $this->assertEquals(10, $data->products[0]->quantity);
    }
}
