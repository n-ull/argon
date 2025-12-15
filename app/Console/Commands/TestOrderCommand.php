<?php

namespace App\Console\Commands;

use Domain\EventManagement\Models\Event;
use Domain\Ordering\Data\CreateOrderData;
use Domain\Ordering\Enums\OrderStatus;
use Domain\Ordering\Services\OrderService;
use Domain\ProductCatalog\Models\Product;
use Illuminate\Console\Command;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class TestOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:test {event_id : The ID of the event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test completed order interactively';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $eventId = $this->argument('event_id');

        // Validate event exists
        $event = Event::find($eventId);
        if (!$event) {
            $this->error("❌ Event with ID {$eventId} not found.");
            return 1;
        }

        $this->info("🎉 Creating test order for event: {$event->title}");
        $this->newLine();

        // Load products with prices
        $products = Product::where('event_id', $eventId)
            ->with('product_prices')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('⚠️  No products found for this event.');
            return 1;
        }

        $selectedItems = [];
        $addMore = true;

        while ($addMore) {
            // Build a flattened list of product-price combinations
            $options = [];
            $productPriceMap = [];

            foreach ($products as $product) {
                if ($product->product_prices->isEmpty()) {
                    continue;
                }

                foreach ($product->product_prices as $price) {
                    $key = "{$product->id}:{$price->id}";

                    // Build the display label
                    $label = $product->name;

                    if ($price->label) {
                        $label .= " - {$price->label}";
                    }

                    $label .= " (\${$price->price})";

                    // Add stock info if available
                    if ($price->stock !== null) {
                        $available = $price->stock - $price->quantity_sold;
                        $label .= " [Stock: {$available}]";

                        if ($available <= 0) {
                            $label .= " SOLD OUT";
                        }
                    }

                    $options[$key] = $label;
                    $productPriceMap[$key] = [
                        'product' => $product,
                        'price' => $price,
                    ];
                }
            }

            if (empty($options)) {
                $this->warn('⚠️  No available product prices found for this event.');
                break;
            }

            // Select product-price combination
            $selection = select(
                label: 'Select a product and price',
                options: $options,
            );

            if (!$selection) {
                $this->warn('No product selected.');
                break;
            }

            $product = $productPriceMap[$selection]['product'];
            $productPrice = $productPriceMap[$selection]['price'];

            // Determine min/max quantity
            $minQuantity = $product->min_per_order ?? 1;
            $maxQuantity = $product->max_per_order ?? 999;

            // If product has stock, limit max quantity to available stock
            if ($productPrice->stock !== null) {
                $availableStock = $productPrice->stock - $productPrice->quantity_sold;
                $maxQuantity = min($maxQuantity, $availableStock);

                if ($availableStock <= 0) {
                    $this->warn("⚠️  This price option is sold out.");
                    continue;
                }
            }

            // Get quantity
            $quantity = text(
                label: 'Enter quantity',
                default: (string) $minQuantity,
                validate: function ($value) use ($minQuantity, $maxQuantity) {
                    if (!is_numeric($value) || $value < 1) {
                        return 'Quantity must be a positive number.';
                    }
                    if ($value < $minQuantity) {
                        return "Minimum quantity is {$minQuantity}.";
                    }
                    if ($value > $maxQuantity) {
                        return "Maximum quantity is {$maxQuantity}.";
                    }
                    return null;
                }
            );

            // Add to selected items
            $selectedItems[] = [
                'productId' => $product->id,
                'productPriceId' => $productPrice->id,
                'quantity' => (int) $quantity,
                'productName' => $product->name,
                'priceLabel' => $productPrice->label ?? 'Standard',
                'unitPrice' => $productPrice->price,
            ];

            $priceLabel = $productPrice->label ?? 'Standard';
            $this->info("✅ Added {$quantity}x {$product->name} ({$priceLabel})");
            $this->newLine();

            // Ask if user wants to add more products
            $addMore = confirm(
                label: 'Add another product?',
                default: false
            );

            $this->newLine();
        }

        if (empty($selectedItems)) {
            $this->warn('⚠️  No items selected. Order creation cancelled.');
            return 1;
        }

        // Display order summary
        $this->info('📋 Order Summary:');
        $this->table(
            ['Product', 'Price Option', 'Quantity', 'Unit Price', 'Subtotal'],
            collect($selectedItems)->map(fn($item) => [
                $item['productName'],
                $item['priceLabel'],
                $item['quantity'],
                '$' . number_format($item['unitPrice'], 2),
                '$' . number_format($item['unitPrice'] * $item['quantity'], 2),
            ])->toArray()
        );
        $this->newLine();

        // Create order using OrderService
        try {
            $orderData = new CreateOrderData(
                eventId: $eventId,
                items: collect($selectedItems)->map(fn($item) => [
                    'productId' => $item['productId'],
                    'productPriceId' => $item['productPriceId'],
                    'quantity' => $item['quantity'],
                ])->toArray(),
                userId: null,
                gateway: 'test'
            );

            $this->info('🔄 Creating order...');
            $order = $orderService->createPendingOrder($orderData);

            // Mark order as completed for testing
            $orderService->completePendingOrder($order->id);

            $this->newLine();
            $this->info('✨ Test order created successfully!');
            $this->info("📦 Order ID: {$order->id}");
            $this->info("🔖 Reference ID: {$order->reference_id}");
            $this->info("💰 Subtotal: \$" . number_format($order->subtotal, 2));

            if ($order->taxes_total > 0) {
                $this->info("💵 Taxes: \$" . number_format($order->taxes_total, 2));
            }

            if ($order->fees_total > 0) {
                $this->info("💳 Fees: \$" . number_format($order->fees_total, 2));
            }

            $this->info("💎 Total: \$" . number_format($order->total, 2));
            $this->info("✅ Status: COMPLETED");

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error creating order: ' . $e->getMessage());
            return 1;
        }
    }
}
