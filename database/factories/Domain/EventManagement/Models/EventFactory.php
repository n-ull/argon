<?php

namespace Database\Factories\Domain\EventManagement\Models;

use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'location_info' => [
                'address' => $this->faker->address,
                'city' => $this->faker->city,
                'country' => $this->faker->country,
            ],
            'status' => EventStatus::PUBLISHED,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(11),
            'organizer_id' => Organizer::factory(), // Need OrganizerFactory too?
            'is_featured' => false,
            'slug' => $this->faker->slug,
        ];
    }
}
