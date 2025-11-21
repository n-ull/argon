<?php

/**
 * PoblateSeeder - Comprehensive Database Population Seeder
 * 
 * This seeder populates the database with a large dataset for testing and development:
 * - 500 users
 * - 4 organizations with settings
 * - 5-15 managers per organization
 * - 8-15 events per organization (with varied dates: past, present, future)
 * - 3-8 products per event (tickets and general items)
 * - 1-4 price tiers per product (with staggered sale dates)
 * 
 * Usage:
 *   php artisan db:seed --class=PoblateSeeder
 * 
 * Or add to DatabaseSeeder:
 *   $this->call([PoblateSeeder::class]);
 */

namespace Database\Seeders;

use App\Models\User;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\EventCategory;
use Domain\OrganizerManagement\Models\Organizer;
use Domain\OrganizerManagement\Models\OrganizerSettings;
use Domain\ProductCatalog\Enums\ProductType;
use Domain\ProductCatalog\Models\Product;
use Domain\ProductCatalog\Models\ProductPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PoblateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting data population...');

        // Create 80 users
        $this->command->info('Creating 80 users...');
        $users = User::factory(80)->create();
        $this->command->info('✓ Users created');

        // Get or create event categories
        $this->command->info('Checking event categories...');
        $categories = $this->getOrCreateEventCategories();
        $this->command->info('✓ Event categories ready');

        // Create 4 organizations with events and products
        $this->command->info('Creating 4 organizations...');
        for ($i = 1; $i <= 4; $i++) {
            $this->createOrganizationWithData($i, $users, $categories);
        }
        $this->command->info('✓ Organizations created with events and products');

        $this->command->info('Data population completed successfully!');
    }

    /**
     * Get existing categories or create new ones if none exist
     */
    private function getOrCreateEventCategories(): array
    {
        $existingCategories = EventCategory::all();
        
        if ($existingCategories->isNotEmpty()) {
            return $existingCategories->toArray();
        }

        $categoryData = [
            ['name' => 'Music & Concerts', 'color' => '#FF6B6B', 'icon' => '🎵'],
            ['name' => 'Sports & Fitness', 'color' => '#4ECDC4', 'icon' => '⚽'],
            ['name' => 'Arts & Culture', 'color' => '#95E1D3', 'icon' => '🎨'],
            ['name' => 'Food & Drink', 'color' => '#F38181', 'icon' => '🍽️'],
            ['name' => 'Business & Professional', 'color' => '#AA96DA', 'icon' => '💼'],
            ['name' => 'Technology', 'color' => '#5C7CFA', 'icon' => '💻'],
            ['name' => 'Health & Wellness', 'color' => '#51CF66', 'icon' => '🧘'],
            ['name' => 'Education', 'color' => '#FFA94D', 'icon' => '📚'],
        ];

        $categories = [];
        foreach ($categoryData as $data) {
            $categories[] = EventCategory::create($data);
        }

        return $categories;
    }

    /**
     * Create an organization with users, events, and products
     */
    private function createOrganizationWithData(int $index, $users, array $categories): void
    {
        // Create organizer
        $organizer = Organizer::create([
            'name' => fake()->company() . ' Events',
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'logo' => fake()->imageUrl(640, 480, 'business', true),
        ]);

        // Create organizer settings
        OrganizerSettings::create([
            'organizer_id' => $organizer->id,
            'raise_money_method' => fake()->randomElement(['internal', 'split']),
            'is_modo_active' => fake()->boolean(30),
            'is_mercadopago_active' => fake()->boolean(50),
        ]);

        // Assign 5-15 users to manage this organizer
        $managerCount = fake()->numberBetween(5, 15);
        $managers = $users->random($managerCount);
        
        // Attach users to organizer with pivot data
        foreach ($managers as $manager) {
            $organizer->users()->attach($manager->id, [
                'is_admin' => fake()->boolean(30), // 30% chance of being admin
                'joined_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("  Organization {$index}: {$organizer->name} ({$managerCount} managers)");

        // Create 8-15 events per organizer
        $eventCount = fake()->numberBetween(8, 15);
        for ($j = 0; $j < $eventCount; $j++) {
            $this->createEventWithProducts($organizer, $categories);
        }
    }

    /**
     * Create an event with products
     */
    private function createEventWithProducts(Organizer $organizer, array $categories): void
    {
        // Generate event dates with variety
        $startDate = $this->generateEventStartDate();
        $duration = fake()->randomElement([2, 3, 4, 6, 8, 12, 24]); // hours
        $endDate = $startDate->copy()->addHours($duration);

        // Create event
        $event = Event::create([
            'title' => fake()->words(fake()->numberBetween(3, 6), true),
            'description' => fake()->paragraphs(fake()->numberBetween(2, 3), true),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_featured' => fake()->boolean(20),
            'slug' => Str::slug(fake()->unique()->words(4, true)),
            'status' => fake()->randomElement([
                EventStatus::PUBLISHED->value,
                EventStatus::PUBLISHED->value, // More published events
                EventStatus::PUBLISHED->value,
                EventStatus::PUBLISHED->value,
                EventStatus::PUBLISHED->value,
                EventStatus::DRAFT->value,
                EventStatus::ARCHIVED->value,
            ]),
            'organizer_id' => $organizer->id,
            'location_info' => [
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'country' => fake()->country(),
                'postal_code' => fake()->postcode(),
                'venue_name' => fake()->company() . ' ' . fake()->randomElement(['Hall', 'Center', 'Arena', 'Theater', 'Stadium']),
            ],
            'event_category_id' => fake()->randomElement($categories)->id,
        ]);

        // Create 3-8 products per event
        $productCount = fake()->numberBetween(3, 8);
        for ($k = 0; $k < $productCount; $k++) {
            $this->createProductWithPrices($event);
        }
    }

    /**
     * Generate varied event start dates
     */
    private function generateEventStartDate(): \Carbon\Carbon
    {
        $dateType = fake()->randomElement(['past', 'near_future', 'future', 'far_future']);
        
        return match ($dateType) {
            'past' => now()->subDays(fake()->numberBetween(1, 90)),
            'near_future' => now()->addDays(fake()->numberBetween(1, 30)),
            'future' => now()->addDays(fake()->numberBetween(31, 90)),
            'far_future' => now()->addDays(fake()->numberBetween(91, 365)),
        };
    }

    /**
     * Create a product with price tiers
     */
    private function createProductWithPrices(Event $event): void
    {
        $productType = fake()->randomElement([
            ProductType::TICKET,
            ProductType::TICKET,
            ProductType::TICKET, // More tickets
            ProductType::GENERAL,
        ]);

        // Calculate sale dates relative to event
        $startSaleDate = $event->start_date->copy()->subDays(fake()->numberBetween(30, 90));
        $endSaleDate = $event->start_date->copy()->subHours(fake()->numberBetween(1, 24));

        $product = Product::create([
            'name' => $this->generateProductName($productType),
            'description' => fake()->paragraph(1),
            'max_per_order' => fake()->numberBetween(4, 10),
            'min_per_order' => 1,
            'product_type' => $productType->value,
            'hide_before_sale_start_date' => fake()->boolean(30),
            'hide_after_sale_end_date' => fake()->boolean(70),
            'hide_when_sold_out' => fake()->boolean(80),
            'show_stock' => fake()->boolean(60),
            'start_sale_date' => $startSaleDate,
            'end_sale_date' => $endSaleDate,
            'event_id' => $event->id,
        ]);

        // Create 1-4 price tiers per product
        $priceCount = fake()->numberBetween(1, 4);
        for ($p = 0; $p < $priceCount; $p++) {
            $this->createProductPrice($product, $p, $priceCount, $startSaleDate, $endSaleDate);
        }
    }

    /**
     * Generate product name based on type
     */
    private function generateProductName(ProductType $type): string
    {
        if ($type === ProductType::TICKET) {
            $ticketTypes = [
                'General Admission',
                'VIP Pass',
                'Early Bird',
                'Student Ticket',
                'Group Package',
                'Premium Seating',
                'Front Row',
                'Balcony',
                'Standing Room',
                'Reserved Seating',
            ];
            return fake()->randomElement($ticketTypes);
        }

        $generalProducts = [
            'Event T-Shirt',
            'Merchandise Bundle',
            'Parking Pass',
            'Food & Beverage Package',
            'Photo Package',
            'Program Book',
            'Commemorative Item',
            'VIP Lounge Access',
        ];
        return fake()->randomElement($generalProducts);
    }

    /**
     * Create a product price tier
     */
    private function createProductPrice(
        Product $product,
        int $index,
        int $totalPrices,
        \Carbon\Carbon $productStartDate,
        \Carbon\Carbon $productEndDate
    ): void {
        $labels = ['Early Bird', 'Regular', 'Premium', 'VIP', 'Standard', 'Deluxe', 'Basic'];
        $basePrice = fake()->randomFloat(2, 10, 500);
        
        // Higher tier = higher price
        $price = $basePrice * (1 + $index * 0.5);

        // Stagger sale dates for different tiers
        if ($totalPrices > 1) {
            $daysBetween = $productStartDate->diffInDays($productEndDate);
            $segmentDays = (int) $daysBetween / $totalPrices;
            
            $tierStartDate = $productStartDate->copy()->addDays($segmentDays * $index);
            $tierEndDate = $index < $totalPrices - 1
                ? $productStartDate->copy()->addDays($segmentDays * ($index + 1))
                : $productEndDate;
        } else {
            $tierStartDate = $productStartDate;
            $tierEndDate = $productEndDate;
        }

        ProductPrice::create([
            'product_id' => $product->id,
            'price' => $price,
            'label' => $labels[$index % \count($labels)],
            'start_sale_date' => $tierStartDate,
            'end_sale_date' => $tierEndDate,
            'stock' => fake()->boolean(70) ? fake()->numberBetween(50, 500) : null,
            'quantity_sold' => fake()->numberBetween(0, 50),
            'is_hidden' => fake()->boolean(10),
            'sort_order' => $index,
        ]);
    }
}
