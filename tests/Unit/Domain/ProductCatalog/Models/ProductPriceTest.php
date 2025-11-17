<?php

use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ProductPrice Model', function () {
    test('can create a product price', function () {
        $product = Product::factory()->create();

        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 99.99,
            'label' => 'Early Bird',
            'start_sale_date' => now(),
            'end_sale_date' => now()->addDays(30),
            'stock' => 100,
            'quantity_sold' => 0,
            'is_hidden' => false,
            'sort_order' => 1,
        ]);

        expect($price)->toBeInstanceOf(ProductPrice::class)
            ->and($price->product_id)->toBe($product->id)
            ->and($price->price)->toBe(99.99)
            ->and($price->label)->toBe('Early Bird')
            ->and($price->stock)->toBe(100)
            ->and($price->quantity_sold)->toBe(0)
            ->and($price->is_hidden)->toBeFalse()
            ->and($price->sort_order)->toBe(1);
    });

    test('belongs to a product', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 50.00,
            'label' => 'Standard',
            'sort_order' => 1,
        ]);

        expect($price->product)->toBeInstanceOf(Product::class)
            ->and($price->product->id)->toBe($product->id);
    });

    test('has correct fillable attributes', function () {
        $price = new ProductPrice();
        $fillable = $price->getFillable();

        expect($fillable)->toContain(
            'product_id',
            'price',
            'label',
            'start_sale_date',
            'end_sale_date',
            'stock',
            'quantity_sold',
            'is_hidden',
            'sort_order'
        );
    });

    test('can handle null stock', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Unlimited',
            'stock' => null,
            'sort_order' => 1,
        ]);

        expect($price->stock)->toBeNull();
    });

    test('can handle null sale dates', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 75.00,
            'label' => 'Standard',
            'start_sale_date' => null,
            'end_sale_date' => null,
            'sort_order' => 1,
        ]);

        expect($price->start_sale_date)->toBeNull()
            ->and($price->end_sale_date)->toBeNull();
    });

    test('can track quantity sold', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'quantity_sold' => 10,
            'sort_order' => 1,
        ]);

        expect($price->quantity_sold)->toBe(10);

        $price->update(['quantity_sold' => 15]);

        expect($price->fresh()->quantity_sold)->toBe(15);
    });

    test('can be hidden', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Hidden Price',
            'is_hidden' => true,
            'sort_order' => 1,
        ]);

        expect($price->is_hidden)->toBeTrue();
    });

    test('can update price', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'sort_order' => 1,
        ]);

        $price->update(['price' => 150.00]);

        expect($price->fresh()->price)->toBe(150.00);
    });

    test('can update stock', function () {
        $product = Product::factory()->create();
        $price = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'Standard',
            'stock' => 50,
            'sort_order' => 1,
        ]);

        $price->update(['stock' => 30]);

        expect($price->fresh()->stock)->toBe(30);
    });
});

describe('ProductPrice Sorting', function () {
    test('sort order determines display order', function () {
        $product = Product::factory()->create();

        $price1 = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 100.00,
            'label' => 'First',
            'sort_order' => 1,
        ]);

        $price2 = ProductPrice::create([
            'product_id' => $product->id,
            'price' => 200.00,
            'label' => 'Second',
            'sort_order' => 2,
        ]);

        expect($price1->sort_order)->toBeLessThan($price2->sort_order);
    });
});