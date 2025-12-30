<?php

namespace Domain\EventManagement\Actions;

use Domain\ProductCatalog\Models\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEventProduct
{
    use AsAction;

    public function handle(Product $product)
    {
        $product->delete();
    }

    public function asController(Product $product)
    {
        $this->handle($product);

        return back()->with(flash_message('Product deleted successfully', 'success'));
    }
}
