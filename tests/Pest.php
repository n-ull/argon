<?php

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Models\Order;
use Domain\Ordering\Models\OrderItem;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function setupAvailableProduct(array $productOverrides = [], array $priceOverrides = [])
{
    $event = Event::factory()->create();

    $product = Product::factory()->create(array_merge([
        'event_id' => $event->id,
        'is_hidden' => false,
    ], $productOverrides));

    ProductPrice::factory()->create(array_merge([
        'product_id' => $product->id,
        'price' => 10,
        'stock' => 10,
    ], $priceOverrides));

    return $product;
}

function setupOrder(array $orderOverrides = [])
{
    $event = Event::factory()->create();

    $product = Product::factory()->create([
        'event_id' => $event->id,
        'is_hidden' => false,
    ]);

    ProductPrice::factory()->create([
        'product_id' => $product->id,
        'price' => 10,
        'stock' => 10,
    ]);

    $order = Order::factory()->create(array_merge([
        'event_id' => $event->id,
        'user_id' => 1,
    ], $orderOverrides));

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 10
    ]);

    return [
        'order' => $order,
        'product' => $product
    ];
}