<?php

namespace Domain\EventManagement\Database\Factories;

use DateInterval;
use DateTimeImmutable;
use Domain\EventManagement\Enums\EventStatus;
use Domain\EventManagement\Models\Event;
use Domain\EventManagement\Models\EventCategory;
use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $startDate = new DateTimeImmutable($this->faker->dateTimeBetween('+30 days', '+3 months')->format('Y-m-d H:i:s'));
        $endDate = $startDate->add(new DateInterval('P1D'));

        return [
            'title' => $this->faker->words(4, true),
            'description' => $this->faker->text(500),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_featured' => $this->faker->boolean(50),
            'slug' => $this->faker->unique()->slug,
            'status' => $this->faker->randomElement([EventStatus::PUBLISHED->value, EventStatus::DRAFT->value, EventStatus::ARCHIVED->value]),
            'organizer_id' => Organizer::factory(),
            'location_info' => [
                'address' => $this->faker->address,
                'city' => $this->faker->city,
                'country' => $this->faker->country,
            ],
            'event_category_id' => EventCategory::factory(),
        ];
    }
}
