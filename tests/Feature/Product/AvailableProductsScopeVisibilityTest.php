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

test('it shows sold out product when hide_when_sold_out is false', function () {
    $product = Product::factory()->create([
        'event_id' => $this->event->id,
        'hide_when_sold_out' => false,
        'is_hidden' => false,
    ]);

    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'stock' => 10,
        'quantity_sold' => 10, // Sold out
    ]);

    $availableProducts = Product::query()
        ->withGlobalScope('available', new AvailableProductsScope)
        ->get();

    expect($availableProducts)->toHaveCount(1);
    expect($availableProducts->first()->id)->toBe($product->id);
});

test('it hides sold out product when hide_when_sold_out is true', function () {
    $product = Product::factory()->create([
        'event_id' => $this->event->id,
        'hide_when_sold_out' => true,
        'is_hidden' => false,
    ]);

    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'stock' => 10,
        'quantity_sold' => 10, // Sold out
    ]);

    $availableProducts = Product::query()
        ->withGlobalScope('available', new AvailableProductsScope)
        ->get();

    expect($availableProducts)->toHaveCount(0);
});

test('it shows sold out price when hide_when_sold_out is false', function () {
    $product = Product::factory()->create([
        'event_id' => $this->event->id,
        'hide_when_sold_out' => false,
        'is_hidden' => false,
    ]);

    $price = ProductPrice::factory()->create([
        'product_id' => $product->id,
        'stock' => 5,
        'quantity_sold' => 5, // Sold out
    ]);

    $availablePrices = ProductPrice::query()
        ->withGlobalScope('available', new AvailableProductPricesScope)
        ->get();

    expect($availablePrices)->toHaveCount(1);
    expect($availablePrices->first()->id)->toBe($price->id);
});

test('it hides sold out price when hide_when_sold_out is true', function () {
    $product = Product::factory()->create([
        'event_id' => $this->event->id,
        'hide_when_sold_out' => true,
        'is_hidden' => false,
    ]);

    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'stock' => 5,
        'quantity_sold' => 5, // Sold out
    ]);

    $availablePrices = ProductPrice::query()
        ->withGlobalScope('available', new AvailableProductPricesScope)
        ->get();

    expect($availablePrices)->toHaveCount(0);
});
