<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Models\Combo;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateEventCombo
{
    use AsAction;

    public function handle(Combo $combo, array $data): Combo
    {
        $combo->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (isset($data['items'])) {
            // Delete existing items
            $combo->items()->forceDelete();

            // Re-create items
            foreach ($data['items'] as $itemData) {
                // Find the default price for the product
                $product = Product::find($itemData['product_id']);
                if (! $product)
                    continue;

                $priceId = $product->product_prices()->first()?->id;

                if ($priceId) {
                    $combo->items()->create([
                        'product_price_id' => $priceId,
                        'quantity' => $itemData['quantity'],
                    ]);
                }
            }
        }

        return $combo;
    }

    public function asController($eventId, $comboId, Request $request)
    {
        // $event = Event::where('slug', $eventId)->first() ?? Event::findOrFail($eventId);
        $combo = Combo::findOrFail($comboId);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $this->handle($combo, $data);

        return redirect()->back()->with('message', flash_success('Combo updated successfully', 'Combo updated successfully'));
    }
}
