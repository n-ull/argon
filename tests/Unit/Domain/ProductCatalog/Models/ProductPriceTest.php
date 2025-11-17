<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ProductCatalog\Models;

use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $expected = [
            'product_id',
            'price',
            'label',
            'start_sale_date',
            'end_sale_date',
            'stock',
            'quantity_sold',
            'is_hidden',
            'sort_order',
        ];

        $productPrice = new ProductPrice;
        $this->assertEquals($expected, $productPrice->getFillable());
    }

    /** @test */
    public function it_belongs_to_a_product(): void
    {
        $product = Product::factory()->create();
        $productPrice = ProductPrice::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $productPrice->product);
        $this->assertEquals($product->id, $productPrice->product->id);
    }

    /** @test */
    public function it_can_create_product_price_with_all_fields(): void
    {
        $product = Product::factory()->create();

        $productPrice = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 99.99,
            'label' => 'Standard Ticket',
            'start_sale_date' => now(),
            'end_sale_date' => now()->addDays(30),
            'stock' => 100,
            'quantity_sold' => 0,
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(ProductPrice::class, $productPrice);
        $this->assertEquals(99.99, $productPrice->price);
        $this->assertEquals('Standard Ticket', $productPrice->label);
        $this->assertEquals(100, $productPrice->stock);
    }

    /** @test */
    public function it_can_store_decimal_prices(): void
    {
        $productPrice = ProductPrice::factory()->create(['price' => 123.45]);

        $this->assertEquals(123.45, $productPrice->price);
    }

    /** @test */
    public function it_can_track_quantity_sold(): void
    {
        $productPrice = ProductPrice::factory()->create([
            'stock' => 100,
            'quantity_sold' => 0,
        ]);

        $this->assertEquals(0, $productPrice->quantity_sold);

        $productPrice->update(['quantity_sold' => 25]);

        $this->assertEquals(25, $productPrice->fresh()->quantity_sold);
    }

    /** @test */
    public function it_can_be_hidden(): void
    {
        $visiblePrice = ProductPrice::factory()->create(['is_hidden' => false]);
        $hiddenPrice = ProductPrice::factory()->create(['is_hidden' => true]);

        $this->assertFalse($visiblePrice->is_hidden);
        $this->assertTrue($hiddenPrice->is_hidden);
    }

    /** @test */
    public function it_can_have_sort_order(): void
    {
        $price1 = ProductPrice::factory()->create(['sort_order' => 1]);
        $price2 = ProductPrice::factory()->create(['sort_order' => 2]);
        $price3 = ProductPrice::factory()->create(['sort_order' => 3]);

        $this->assertEquals(1, $price1->sort_order);
        $this->assertEquals(2, $price2->sort_order);
        $this->assertEquals(3, $price3->sort_order);
    }

    /** @test */
    public function it_can_have_null_stock_for_unlimited(): void
    {
        $productPrice = ProductPrice::factory()->create(['stock' => null]);

        $this->assertNull($productPrice->stock);
    }

    /** @test */
    public function it_can_have_sale_date_range(): void
    {
        $startDate = now();
        $endDate = now()->addDays(15);

        $productPrice = ProductPrice::factory()->create([
            'start_sale_date' => $startDate,
            'end_sale_date' => $endDate,
        ]);

        $this->assertNotNull($productPrice->start_sale_date);
        $this->assertNotNull($productPrice->end_sale_date);
    }

    /** @test */
    public function it_has_timestamps(): void
    {
        $productPrice = ProductPrice::factory()->create();

        $this->assertNotNull($productPrice->created_at);
        $this->assertNotNull($productPrice->updated_at);
    }

    /** @test */
    public function multiple_prices_can_belong_to_same_product(): void
    {
        $product = Product::factory()->create();

        ProductPrice::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertEquals(3, $product->product_prices()->count());
    }
}
