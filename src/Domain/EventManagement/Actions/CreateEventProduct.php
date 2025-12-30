<?php

namespace Domain\EventManagement\Actions;

use Domain\EventManagement\Models\Event;
use Domain\ProductCatalog\Enums\ProductPriceType;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateEventProduct
{
    use AsAction;

    public function handle(Event $event, array $data): Product
    {
        /** @var Product $product */
        $product = $event->products()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'product_type' => $data['product_type'],
            'product_price_type' => $data['product_price_type'],
            'min_per_order' => $data['min_per_order'] ?? 1,
            'max_per_order' => $data['max_per_order'] ?? 10,
            'start_sale_date' => $data['start_sale_date'] ?? null,
            'end_sale_date' => $data['end_sale_date'] ?? null,
            'is_hidden' => $data['is_hidden'] ?? false,
            'hide_when_sold_out' => $data['hide_when_sold_out'] ?? false,
            'hide_before_sale_start_date' => $data['hide_before_sale_start_date'] ?? false,
            'hide_after_sale_end_date' => $data['hide_after_sale_end_date'] ?? false,
            'show_stock' => $data['show_stock'] ?? false,
        ]);

        if (!empty($data['prices'])) {
            foreach ($data['prices'] as $priceData) {
                $product->product_prices()->create([
                    'price' => $priceData['price'],
                    'label' => $priceData['label'] ?? 'Standard Price',
                    'stock' => isset($priceData['has_limited_stock']) && $priceData['has_limited_stock'] ? ($priceData['stock'] ?? null) : null,
                    'start_sale_date' => $priceData['start_sale_date'] ?? null,
                    'end_sale_date' => $priceData['end_sale_date'] ?? null,
                ]);
            }
        }

        return $product;
    }

    public function asController($eventId, Request $request)
    {
        $event = Event::where('slug', $eventId)->first() ?? Event::findOrFail($eventId);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_type' => 'required|string',
            'product_price_type' => 'required|string',
            'min_per_order' => 'nullable|integer|min:1',
            'max_per_order' => 'nullable|integer|min:1',
            'start_sale_date' => 'nullable|date',
            'end_sale_date' => 'nullable|date',
            'is_hidden' => 'boolean',
            'hide_when_sold_out' => 'boolean',
            'hide_before_sale_start_date' => 'boolean',
            'hide_after_sale_end_date' => 'boolean',
            'show_stock' => 'boolean',
            'prices' => 'required|array|min:1',
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.label' => 'nullable|string',
            'prices.*.stock' => 'nullable|integer|min:0',
            'prices.*.has_limited_stock' => 'boolean',
            'prices.*.start_sale_date' => 'nullable|date',
            'prices.*.end_sale_date' => 'nullable|date',
        ]);

        $this->handle($event, $data);

        return redirect()->back();
    }
}
