<?php

namespace Database\Factories\Domain\OrganizerManagement\Models;

use Domain\OrganizerManagement\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizerFactory extends Factory
{
    protected $model = Organizer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'logo' => $this->faker->imageUrl,
        ];
    }
}
