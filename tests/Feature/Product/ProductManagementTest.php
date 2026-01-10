<?php

use App\Models\User;
use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\ProductCatalog\Enums\ProductPriceType;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;

test('updating product to standard removes extra prices', function () {
    $user = User::factory()->create();
    $organizer = Organizer::factory()->create(['owner_id' => $user->id]);
    $organizer->users()->attach($user);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    // Create a product with multiple prices (e.g. erroneously created or staggered)
    $product = Product::factory()->create([
        'event_id' => $event->id,
        'product_price_type' => ProductPriceType::STAGGERED->value,
    ]);

    ProductPrice::factory()->count(3)->create([
        'product_id' => $product->id,
    ]);

    expect($product->refresh()->product_prices)->toHaveCount(3);

    $response = $this->actingAs($user)->put(route('manage.event.products.update', ['event' => $event, 'product' => $product]), [
        'name' => 'Updated Product',
        'product_type' => 'general',
        'product_price_type' => 'standard',
        'min_per_order' => 1,
        'max_per_order' => 10,
        'is_hidden' => false,
        'hide_when_sold_out' => false,
        'prices' => [
            [
                'id' => $product->product_prices->first()->id,
                'price' => 50,
                'stock' => 10,
                'has_limited_stock' => true,
                'label' => 'Standard Price',
            ],
        ],
    ]);

    $response->assertRedirect();

    $product->refresh();
    expect($product->product_price_type)->toBe('standard');
    expect($product->product_prices)->toHaveCount(1);
    expect($product->product_prices->first()->price)->toEqual(50);
});

test('can create a standard product', function () {
    $user = User::factory()->create();
    $organizer = Organizer::factory()->create(['owner_id' => $user->id]);
    $organizer->users()->attach($user);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    $response = $this->actingAs($user)->post(route('manage.event.products.store', $event), [
        'name' => 'New Product',
        'product_type' => 'general',
        'product_price_type' => 'standard',
        'min_per_order' => 1,
        'max_per_order' => 10,
        'is_hidden' => false,
        'hide_when_sold_out' => false,
        'prices' => [
            [
                'price' => 100,
                'stock' => 50,
                'has_limited_stock' => true,
                'label' => 'Standard Price',
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'product_price_type' => 'standard',
        'event_id' => $event->id,
    ]);

    $product = Product::where('name', 'New Product')->first();
    expect($product->product_prices)->toHaveCount(1);
    expect($product->product_prices->first()->price)->toEqual(100);
});

test('can create a staggered product', function () {
    $user = User::factory()->create();
    $organizer = Organizer::factory()->create(['owner_id' => $user->id]);
    $organizer->users()->attach($user);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    $response = $this->actingAs($user)->post(route('manage.event.products.store', $event), [
        'name' => 'Staggered Product',
        'product_type' => 'general',
        'product_price_type' => 'staggered',
        'min_per_order' => 1,
        'max_per_order' => 10,
        'is_hidden' => false,
        'hide_when_sold_out' => false,
        'prices' => [
            [
                'price' => 80,
                'stock' => 100,
                'has_limited_stock' => true,
                'label' => 'Early Bird',
            ],
            [
                'price' => 120,
                'stock' => null,
                'has_limited_stock' => false,
                'label' => 'Regular',
            ],
        ],
    ]);

    $response->assertRedirect();

    $product = Product::where('name', 'Staggered Product')->first();
    expect($product->product_price_type)->toBe('staggered');
    expect($product->product_prices)->toHaveCount(2);
    expect($product->product_prices->pluck('label')->toArray())->toContain('Early Bird', 'Regular');
});
