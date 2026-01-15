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

            if ($product->tickets()->exists()) {
                return false;
            }

            $product->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function asController($eventId, $productId)
    {
        $result = $this->handle($eventId, $productId);

        if (! $result) {
            return back()->with('message', flash_error('Product cannot be deleted', 'Product cannot be deleted because it has tickets'));
        }

        return back()->with('message', flash_success('Product deleted successfully', 'Product deleted successfully'));
    }
}
