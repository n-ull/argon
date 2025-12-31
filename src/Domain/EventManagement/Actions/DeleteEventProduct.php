<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventProduct
{
    use AsAction;

    public function handle($eventId, $productId)
    {
        try {
            $product = Product::findOrFail($productId);
            $product->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // TODO: if tickets exists, cant delete product
    public function asController($eventId, $productId)
    {
        $this->handle($eventId, $productId);

        return back()->with('message', flash_success('Product deleted successfully', 'Product deleted successfully'));
    }
}
