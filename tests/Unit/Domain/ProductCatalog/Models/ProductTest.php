<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ProductCatalog\Models;

use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Domain\EventManagement\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes(): void
    {
        $expected = [
            'name',
            'description',
            'max_per_order',
            'min_per_order',
            'product_type',
            'product_price_type',
            'hide_before_sale_start_date',
            'hide_after_sale_end_date',
            'hide_when_sold_out',
            'show_stock',
            'start_sale_date',
            'end_sale_date',
            'event_id',
        ];

        $product = new Product();
        $this->assertEquals($expected, $product->getFillable());
    }

    /** @test */
    public function it_casts_product_type_to_enum(): void
    {
        $product = new Product();
        $casts = $product->getCasts();

        $this->assertArrayHasKey('product_type', $casts);
        $this->assertEquals(ProductType::class, $casts['product_type']);
    }

    /** @test */
    public function it_has_product_prices_relationship(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $product->product_prices()
        );
    }

    /** @test */
    public function it_orders_product_prices_by_sort_order(): void
    {
        $product = Product::factory()->create();
        
        ProductPrice::factory()->create(['product_id' => $product->id, 'sort_order' => 3]);
        ProductPrice::factory()->create(['product_id' => $product->id, 'sort_order' => 1]);
        ProductPrice::factory()->create(['product_id' => $product->id, 'sort_order' => 2]);

        $prices = $product->product_prices;

        $this->assertEquals(1, $prices[0]->sort_order);
        $this->assertEquals(2, $prices[1]->sort_order);
        $this->assertEquals(3, $prices[2]->sort_order);
    }

    /** @test */
    public function it_can_create_product_with_all_fields(): void
    {
        $event = Event::factory()->create();
        
        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'max_per_order' => 10,
            'min_per_order' => 1,
            'product_type' => ProductType::TICKET,
            'product_price_type' => 'standard',
            'hide_before_sale_start_date' => true,
            'hide_after_sale_end_date' => false,
            'hide_when_sold_out' => true,
            'show_stock' => false,
            'start_sale_date' => now(),
            'end_sale_date' => now()->addDays(30),
            'event_id' => $event->id,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(ProductType::TICKET, $product->product_type);
    }

    /** @test */
    public function it_can_have_general_or_ticket_type(): void
    {
        $generalProduct = Product::factory()->create(['product_type' => ProductType::GENERAL]);
        $ticketProduct = Product::factory()->create(['product_type' => ProductType::TICKET]);

        $this->assertEquals(ProductType::GENERAL, $generalProduct->product_type);
        $this->assertEquals(ProductType::TICKET, $ticketProduct->product_type);
    }

    /** @test */
    public function it_can_have_visibility_settings(): void
    {
        $product = Product::factory()->create([
            'hide_before_sale_start_date' => true,
            'hide_after_sale_end_date' => true,
            'hide_when_sold_out' => false,
            'show_stock' => true,
        ]);

        $this->assertTrue($product->hide_before_sale_start_date);
        $this->assertTrue($product->hide_after_sale_end_date);
        $this->assertFalse($product->hide_when_sold_out);
        $this->assertTrue($product->show_stock);
    }

    /** @test */
    public function it_can_have_quantity_limits(): void
    {
        $product = Product::factory()->create([
            'min_per_order' => 2,
            'max_per_order' => 5,
        ]);

        $this->assertEquals(2, $product->min_per_order);
        $this->assertEquals(5, $product->max_per_order);
    }

    /** @test */
    public function it_belongs_to_an_event(): void
    {
        $event = Event::factory()->create();
        $product = Product::factory()->create(['event_id' => $event->id]);

        $this->assertEquals($event->id, $product->event_id);
    }

    /** @test */
    public function it_has_sale_date_range(): void
    {
        $startDate = now();
        $endDate = now()->addDays(30);

        $product = Product::factory()->create([
            'start_sale_date' => $startDate,
            'end_sale_date' => $endDate,
        ]);

        $this->assertNotNull($product->start_sale_date);
        $this->assertNotNull($product->end_sale_date);
    }
}