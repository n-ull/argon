<?php

namespace App\Console\Commands;

use Domain\ProductCatalog\Models\Product;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class MigrateToStaggeredPricing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:migrate-to-staggered {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find products with multiple product prices and update them to staggered pricing type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Searching for products with multiple prices...');
        $this->newLine();

        // Find products with more than one product price
        $productsWithMultiplePrices = Product::withCount('product_prices')
            ->with(['product_prices', 'event'])
            ->having('product_prices_count', '>', 1)
            ->get();

        if ($productsWithMultiplePrices->isEmpty()) {
            $this->info('✅ No products found with multiple prices.');

            return 0;
        }

        // Filter out products that are already staggered
        $productsToUpdate = $productsWithMultiplePrices->filter(function ($product) {
            return $product->product_price_type !== 'staggered';
        });

        if ($productsToUpdate->isEmpty()) {
            $this->info('✅ All products with multiple prices are already set to staggered pricing.');
            $this->info("ℹ️  Found {$productsWithMultiplePrices->count()} product(s) with multiple prices (all already staggered).");

            return 0;
        }

        // Display products that will be updated
        $this->info("📋 Found {$productsToUpdate->count()} product(s) with multiple prices that need updating:");
        $this->newLine();

        $tableData = $productsToUpdate->map(function ($product) {
            return [
                'ID' => $product->id,
                'Name' => $product->name,
                'Event' => $product->event ? $product->event->title : 'N/A',
                'Price Count' => $product->product_prices_count,
                'Current Type' => $product->product_price_type ?? 'null',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Event', 'Price Count', 'Current Type'],
            $tableData
        );

        $this->newLine();

        // Show price details for each product
        foreach ($productsToUpdate as $product) {
            $this->info("📦 {$product->name} (ID: {$product->id})");
            $priceData = $product->product_prices->map(function ($price) {
                return [
                    'Label' => $price->label ?? '(no label)',
                    'Price' => '$'.number_format($price->price, 2),
                    'Stock' => $price->stock ?? 'unlimited',
                ];
            })->toArray();

            $this->table(['Label', 'Price', 'Stock'], $priceData);
            $this->newLine();
        }

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->warn('🔍 DRY RUN MODE: No changes will be made.');
            $this->info("Would update {$productsToUpdate->count()} product(s) to staggered pricing.");

            return 0;
        }

        // Ask for confirmation
        $confirmed = confirm(
            label: "Update {$productsToUpdate->count()} product(s) to staggered pricing?",
            default: false
        );

        if (! $confirmed) {
            $this->warn('❌ Operation cancelled.');

            return 1;
        }

        // Update products
        $this->info('🔄 Updating products...');
        $updated = 0;

        foreach ($productsToUpdate as $product) {
            try {
                $product->update(['product_price_type' => 'staggered']);
                $this->info("✅ Updated: {$product->name} (ID: {$product->id})");
                $updated++;
            } catch (\Exception $e) {
                $this->error("❌ Failed to update {$product->name} (ID: {$product->id}): {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✨ Successfully updated {$updated} product(s) to staggered pricing!");

        return 0;
    }
}
