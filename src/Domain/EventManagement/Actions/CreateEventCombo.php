<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Models\Combo;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEventCombo
{
    use AsAction;

    public function handle(Event $event, array $data): Combo
    {
        /** @var Combo $combo */
        $combo = $event->combos()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['items'])) {
            foreach ($data['items'] as $itemData) {
                // Find the default price for the product if not specified
                // Assuming simplified logic: grab the first available price for the product
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

    public function asController($eventId, Request $request)
    {
        $event = Event::where('slug', $eventId)->first() ?? Event::findOrFail($eventId);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'items' => 'array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $this->handle($event, $data);

        return redirect()->back()->with('message', flash_success('Combo created successfully', 'Combo created successfully'));
    }
}
