<?php

namespace Domain\EventManagement\Database\Factories;

use Domain\EventManagement\Models\EventCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventCategoryFactory extends Factory
{
    protected $model = EventCategory::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'icon' => $this->faker->word,
            'color' => $this->faker->colorName,
        ];
    }
}
