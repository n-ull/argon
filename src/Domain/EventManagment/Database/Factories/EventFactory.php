<?php

namespace Domain\EventManagment\Database\Factories;

use DateInterval;
use Domain\EventManagment\Models\Event;
use Domain\OrganizerManagment\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween("+30 days", "+3 months");

        return [
            "title" => $this->faker->words(4, true),
            "description" => $this->faker->text(1000),
            "start_date" => $startDate,
            "end_date" => $startDate->add(new DateInterval('P1D')),
            "is_featured" => $this->faker->boolean(50),
            "slug" => $this->faker->slug,
            "status" => $this->faker->randomElement(["published", "draft", "archived"]),
            "organizer_id" => Organizer::factory()->create()->id,
            "location_info" => [
                "address" => $this->faker->address,
                "city"=> $this->faker->city,
                "country" => $this->faker->country,
            ]
        ];
    }
}
