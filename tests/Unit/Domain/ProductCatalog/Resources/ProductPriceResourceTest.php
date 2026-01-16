<?php

namespace Tests\Unit\Domain\ProductCatalog\Resources;

use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Domain\ProductCatalog\Resources\ProductPriceResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_limit_max_per_order_when_stock_is_higher_than_max_per_order()
    {
        $product = Product::factory()->create(['max_per_order' => 5]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'quantity_sold' => 0,
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(5, $resource['limit_max_per_order']);
    }

    public function test_it_returns_correct_limit_max_per_order_when_stock_is_lower_than_max_per_order()
    {
        $product = Product::factory()->create(['max_per_order' => 5]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 3,
            'quantity_sold' => 0,
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(3, $resource['limit_max_per_order']);
    }

    public function test_it_returns_correct_limit_max_per_order_when_some_are_already_sold()
    {
        $product = Product::factory()->create(['max_per_order' => 5]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'quantity_sold' => 8, // 2 left
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(2, $resource['limit_max_per_order']);
    }

    public function test_it_returns_correct_limit_max_per_order_when_exactly_at_limit()
    {
        $product = Product::factory()->create(['max_per_order' => 5]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'quantity_sold' => 0,
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(5, $resource['limit_max_per_order']);
    }

    public function test_it_returns_zero_when_sold_out()
    {
        $product = Product::factory()->create(['max_per_order' => 5]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'quantity_sold' => 5,
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(0, $resource['limit_max_per_order']);
    }

    public function test_it_uses_max_per_order_when_stock_is_null()
    {
        $product = Product::factory()->create(['max_per_order' => 10]);
        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => null,
            'quantity_sold' => 100,
        ]);

        $price->load('product');

        $resource = (new ProductPriceResource($price))->toArray(request());

        $this->assertEquals(10, $resource['limit_max_per_order']);
    }
}
