<?php

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Model', function () {
    test('can create a product', function () {
        $event = Event::factory()->create();

        $product = Product::create([
            'name' => 'Test Ticket',
            'description' => 'Test Description',
            'max_per_order' => 10,
            'min_per_order' => 1,
            'product_type' => ProductType::TICKET,
            'hide_before_sale_start_date' => false,
            'hide_after_sale_end_date' => false,
            'hide_when_sold_out' => true,
            'show_stock' => true,
            'start_sale_date' => now(),
            'end_sale_date' => now()->addDays(30),
            'event_id' => $event->id,
        ]);

        expect($product)->toBeInstanceOf(Product::class)
            ->and($product->name)->toBe('Test Ticket')
            ->and($product->description)->toBe('Test Description')
            ->and($product->max_per_order)->toBe(10)
            ->and($product->min_per_order)->toBe(1)
            ->and($product->product_type)->toBe(ProductType::TICKET)
            ->and($product->event_id)->toBe($event->id);
    });

    test('casts product_type to ProductType enum', function () {
        $product = Product::factory()->create(['product_type' => 'general']);

        expect($product->product_type)->toBeInstanceOf(ProductType::class)
            ->and($product->product_type)->toBe(ProductType::GENERAL);
    });

    test('has many product prices', function () {
        $product = Product::factory()->create();

        $price1 = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'sort_order' => 1,
        ]);

        $price2 = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 150.00,
            'label' => 'VIP',
            'stock' => 20,
            'sort_order' => 2,
        ]);

        $product->load('product_prices');

        expect($product->product_prices)->toHaveCount(2)
            ->and($product->product_prices->pluck('id')->toArray())->toContain($price1->id, $price2->id);
    });

    test('product prices are ordered by sort_order', function () {
        $product = Product::factory()->create();

        ProductPrice::create([
            'product_id' => $product->id,
            'price' => 200.00,
            'label' => 'Premium',
            'sort_order' => 3,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'sort_order' => 1,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'price' => 150.00,
            'label' => 'VIP',
            'sort_order' => 2,
        ]);

        $product->load('product_prices');

        expect($product->product_prices->pluck('label')->toArray())
            ->toBe(['Standard', 'VIP', 'Premium']);
    });

    test('has correct fillable attributes', function () {
        $product = new Product();
        $fillable = $product->getFillable();

        expect($fillable)->toContain(
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
            'event_id'
        );
    });

    test('can handle boolean visibility flags', function () {
        $product = Product::factory()->create([
            'hide_before_sale_start_date' => true,
            'hide_after_sale_end_date' => true,
            'hide_when_sold_out' => true,
            'show_stock' => false,
        ]);

        expect($product->hide_before_sale_start_date)->toBeTrue()
            ->and($product->hide_after_sale_end_date)->toBeTrue()
            ->and($product->hide_when_sold_out)->toBeTrue()
            ->and($product->show_stock)->toBeFalse();
    });

    test('can update product attributes', function () {
        $product = Product::factory()->create(['name' => 'Old Name']);

        $product->update(['name' => 'New Name']);

        expect($product->fresh()->name)->toBe('New Name');
    });
});

describe('Product Types', function () {
    test('can be general type', function () {
        $product = Product::factory()->create(['product_type' => ProductType::GENERAL]);

        expect($product->product_type)->toBe(ProductType::GENERAL);
    });

    test('can be ticket type', function () {
        $product = Product::factory()->create(['product_type' => ProductType::TICKET]);

        expect($product->product_type)->toBe(ProductType::TICKET);
    });
});

describe('Product Constraints', function () {
    test('enforces min and max per order', function () {
        $product = Product::factory()->create([
            'min_per_order' => 2,
            'max_per_order' => 5,
        ]);

        expect($product->min_per_order)->toBe(2)
            ->and($product->max_per_order)->toBe(5);
    });

    test('min per order can be 1', function () {
        $product = Product::factory()->create(['min_per_order' => 1]);

        expect($product->min_per_order)->toBe(1);
    });

    test('max per order can be high', function () {
        $product = Product::factory()->create(['max_per_order' => 100]);

        expect($product->max_per_order)->toBe(100);
    });
});