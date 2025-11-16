<?php

namespace Domain\OrganizerManagment\Database\Factories;

use Domain\OrganizerManagment\Models\Organizer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizerFactory extends Factory
{
    protected $model = Organizer::class;

    public function definition()
    {
        return [
            "name" => $this->faker->name,
            "email"=> $this->faker->safeEmail,
            "phone"=> $this->faker->phoneNumber,
            "logo" => $this->faker->image
        ];
    }
}
