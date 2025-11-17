<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering\Data;

use Domain\Ordering\Data\CreateOrderProductData;
use PHPUnit\Framework\TestCase;

class CreateOrderProductDataTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $data = new CreateOrderProductData(
            productId: 1,
            selectedPriceId: 10,
            quantity: 5
        );

        $this->assertEquals(1, $data->productId);
        $this->assertEquals(10, $data->selectedPriceId);
        $this->assertEquals(5, $data->quantity);
    }

    /** @test */
    public function it_handles_different_product_ids(): void
    {
        $data = new CreateOrderProductData(999, 888, 1);

        $this->assertEquals(999, $data->productId);
    }

    /** @test */
    public function it_handles_different_price_ids(): void
    {
        $data = new CreateOrderProductData(1, 777, 1);

        $this->assertEquals(777, $data->selectedPriceId);
    }

    /** @test */
    public function it_handles_different_quantities(): void
    {
        $data = new CreateOrderProductData(1, 10, 100);

        $this->assertEquals(100, $data->quantity);
    }

    /** @test */
    public function it_handles_minimum_quantity_of_one(): void
    {
        $data = new CreateOrderProductData(5, 50, 1);

        $this->assertEquals(1, $data->quantity);
    }

    /** @test */
    public function it_stores_all_properties_as_integers(): void
    {
        $data = new CreateOrderProductData(1, 2, 3);

        $this->assertIsInt($data->productId);
        $this->assertIsInt($data->selectedPriceId);
        $this->assertIsInt($data->quantity);
    }
}
