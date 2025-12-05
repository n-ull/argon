<?php

namespace Domain\EventManagement\Database\Seeders;

use Domain\EventManagement\Models\EventCategory;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Music',
                'icon' => 'music',
                'color' => 'green',
            ],
            [
                'name' => 'Theater',
                'icon' => 'theater',
                'color' => 'red',
            ],
            [
                'name' => 'Cinema',
                'icon' => 'film',
                'color' => 'blue',
            ],
        ];

        EventCategory::factory()
            ->count(3)
            ->sequence(...$categories)
            ->create();
    }
}
