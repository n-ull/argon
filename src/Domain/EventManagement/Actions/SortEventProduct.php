<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Product;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class SortEventProduct
{
    use AsAction;

    public function handle($productId, $direction)
    {
        $product = Product::findOrFail($productId);

        $products = Product::where('event_id', $product->event_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $products->search(fn($p) => $p->id == $product->id);
        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (isset($products[$targetIndex])) {
            $otherProduct = $products[$targetIndex];

            // Ensure all products have unique, sequential sort orders based on their current list position
            foreach ($products as $index => $p) {
                $p->update(['sort_order' => $index]);
            }

            // Perform the swap
            $product->update(['sort_order' => $targetIndex]);
            $otherProduct->update(['sort_order' => $currentIndex]);
        }

        return $product;
    }

    public function asController($eventId, $productId, Request $request)
    {
        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $this->handle($productId, $validated['direction']);

        return back()->with('message', flash_success('Product sorted successfully', 'Product sorted successfully'));
    }
}
