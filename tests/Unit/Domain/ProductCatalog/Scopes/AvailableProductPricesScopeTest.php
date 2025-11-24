<?php

namespace Tests\Unit\Domain\ProductCatalog\Scopes;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Domain\ProductCatalog\Scopes\AvailableProductPricesScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailableProductPricesScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_filters_prices_based_on_visibility_dates_and_stock()
    {
        $event = Event::factory()->create([
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(10),
        ]);

        $product = Product::factory()->create([
            'event_id' => $event->id,
            'show_stock' => true,
            'hide_after_sale_end_date' => true,
            'hide_when_sold_out' => true,
        ]);

        // 1. Available: Standard valid price
        $price1 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'is_hidden' => false,
            'start_sale_date' => null,
            'end_sale_date' => null,
            'stock' => 10,
            'quantity_sold' => 0,
        ]);

        // 2. Unavailable: Hidden
        $price2 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'is_hidden' => true,
        ]);

        // 3. Visible but not available: Future start date
        $price3 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'start_sale_date' => now()->addDay(),
        ]);

        // 4. Unavailable: Past end date
        $price4 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'end_sale_date' => now()->subDay(),
        ]);

        // 5. Unavailable: No Stock (when show_stock is true)
        $price5 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
            'quantity_sold' => 5,
        ]);

        // 6. Available: No Stock (when show_stock is false)
        $productNoStock = Product::factory()->create([
            'event_id' => $event->id,
            'show_stock' => false,
        ]);
        $price6 = ProductPrice::factory()->create([
            'product_id' => $productNoStock->id,
            'stock' => 5,
            'quantity_sold' => 5,
        ]);

        // Apply Scope
        $results = ProductPrice::query()->withGlobalScope('available', new AvailableProductPricesScope)->get();

        $this->assertTrue($results->contains($price1), 'Price 1 should be available');
        $this->assertFalse($results->contains($price2), 'Price 2 should be unavailable (hidden)');
        $this->assertTrue($results->contains($price3), 'Price 3 should be unavailable (future start)');
        $this->assertFalse($results->contains($price4), 'Price 4 should be unavailable (past end)');
        $this->assertFalse($results->contains($price5), 'Price 5 should be unavailable (no stock)');
        $this->assertTrue($results->contains($price6), 'Price 6 should be available (hide_when_sold_out true)');
    }
}
