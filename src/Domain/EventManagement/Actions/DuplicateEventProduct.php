<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class DuplicateEventProduct
{
    use AsAction;

    public function handle($eventId, $productId)
    {
        $product = Product::findOrFail($productId);

        \DB::transaction(function () use ($product) {
            // 1. clone product
            $newProduct = $product->replicate();
            $newProduct->push();

            // 2. clone prices
            foreach ($product->product_prices as $price) {
                $newPrice = $price->replicate();
                $newPrice->product_id = $newProduct->id;
                $newPrice->save();
            }

            return $newProduct;
        });
    }

    public function asController($eventId, $productId)
    {
        $this->handle($eventId, $productId);

        return back()->with('message', flash_success('Product duplicated successfully', 'Product duplicated successfully'));
    }
}
