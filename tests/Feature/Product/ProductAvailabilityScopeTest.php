<?php

use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Domain\ProductCatalog\Scopes\AvailableProductPricesScope;
use Domain\ProductCatalog\Scopes\AvailableProductsScope;

beforeEach(function () {
    $this->organizer = Organizer::factory()->create();
    $this->event = Event::factory()->create([
        'organizer_id' => $this->organizer->id,
        'start_date' => now()->subDays(5),
        'end_date' => now()->addDays(30),
    ]);
});

describe('AvailableProductsScope', function () {
    test('shows product with null end_sale_date when hide_after_sale_end_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => null,
            'hide_after_sale_end_date' => true,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });

    test('shows product with null end_sale_date when hide_after_sale_end_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => null,
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });

    test('hides product with past end_sale_date when hide_after_sale_end_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(10),
            'end_sale_date' => now()->subDays(1),
            'hide_after_sale_end_date' => true,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(0);
    });

    test('shows product with past end_sale_date when hide_after_sale_end_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(10),
            'end_sale_date' => now()->subDays(1),
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });

    test('shows product with future end_sale_date regardless of hide_after_sale_end_date', function () {
        $product1 = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => now()->addDays(5),
            'hide_after_sale_end_date' => true,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        $product2 = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => now()->addDays(5),
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create(['product_id' => $product1->id, 'price' => 100]);
        ProductPrice::factory()->create(['product_id' => $product2->id, 'price' => 100]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(2);
    });

    test('uses event end_date when product end_sale_date is null', function () {
        // Event ends in the past
        $pastEvent = Event::factory()->create([
            'organizer_id' => $this->organizer->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(1),
        ]);

        $product = Product::factory()->create([
            'event_id' => $pastEvent->id,
            'start_sale_date' => now()->subDays(5),
            'end_sale_date' => null, // Will use event's end_date
            'hide_after_sale_end_date' => true,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        // Should be hidden because event end_date is in the past
        expect($availableProducts)->toHaveCount(0);
    });

    test('shows product with null start_sale_date when hide_before_sale_start_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => null,
            'end_sale_date' => now()->addDays(5),
            'hide_before_sale_start_date' => true,
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });

    test('shows product with null start_sale_date when hide_before_sale_start_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => null,
            'end_sale_date' => now()->addDays(5),
            'hide_before_sale_start_date' => false,
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });

    test('hides product with future start_sale_date when hide_before_sale_start_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->addDays(1),
            'end_sale_date' => now()->addDays(10),
            'hide_before_sale_start_date' => true,
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(0);
    });

    test('shows product with future start_sale_date when hide_before_sale_start_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'start_sale_date' => now()->addDays(1),
            'end_sale_date' => now()->addDays(10),
            'hide_before_sale_start_date' => false,
            'hide_after_sale_end_date' => false,
            'is_hidden' => false,
            'show_stock' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);

        $availableProducts = Product::query()
            ->withGlobalScope('available', new AvailableProductsScope)
            ->get();

        expect($availableProducts)->toHaveCount(1);
        expect($availableProducts->first()->id)->toBe($product->id);
    });
});

describe('AvailableProductPricesScope', function () {
    test('shows price with null end_sale_date when hide_after_sale_end_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => true,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => null,
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });

    test('shows price with null end_sale_date when hide_after_sale_end_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => null,
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });

    test('hides price with past end_sale_date when hide_after_sale_end_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => true,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(10),
            'end_sale_date' => now()->subDays(1),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(0);
    });

    test('shows price with past end_sale_date when hide_after_sale_end_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(10),
            'end_sale_date' => now()->subDays(1),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });

    test('shows price with future end_sale_date regardless of hide_after_sale_end_date', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => true,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price1 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => now()->addDays(5),
            'is_hidden' => false,
        ]);

        $price2 = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 150,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => now()->addDays(10),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(2);
    });

    test('multiple prices with mixed null and past dates respect hide_after_sale_end_date', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => true,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        // Price with null end date - should show
        $priceNull = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => null,
            'is_hidden' => false,
        ]);

        // Price with past end date - should hide
        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 150,
            'start_sale_date' => now()->subDays(10),
            'end_sale_date' => now()->subDays(1),
            'is_hidden' => false,
        ]);

        // Price with future end date - should show
        $priceFuture = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 200,
            'start_sale_date' => now()->subDays(1),
            'end_sale_date' => now()->addDays(5),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(2);
        expect($availablePrices->pluck('id')->toArray())->toContain($priceNull->id, $priceFuture->id);
    });

    test('shows price with null start_sale_date when hide_before_sale_start_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => true,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => null,
            'end_sale_date' => now()->addDays(5),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });

    test('shows price with null start_sale_date when hide_before_sale_start_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => null,
            'end_sale_date' => now()->addDays(5),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });

    test('hides price with future start_sale_date when hide_before_sale_start_date is true', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => true,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->addDays(1),
            'end_sale_date' => now()->addDays(10),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(0);
    });

    test('shows price with future start_sale_date when hide_before_sale_start_date is false', function () {
        $product = Product::factory()->create([
            'event_id' => $this->event->id,
            'hide_after_sale_end_date' => false,
            'hide_before_sale_start_date' => false,
            'show_stock' => false,
            'hide_when_sold_out' => false,
        ]);

        $price = ProductPrice::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
            'start_sale_date' => now()->addDays(1),
            'end_sale_date' => now()->addDays(10),
            'is_hidden' => false,
        ]);

        $availablePrices = ProductPrice::query()
            ->withGlobalScope('available', new AvailableProductPricesScope)
            ->get();

        expect($availablePrices)->toHaveCount(1);
        expect($availablePrices->first()->id)->toBe($price->id);
    });
});
