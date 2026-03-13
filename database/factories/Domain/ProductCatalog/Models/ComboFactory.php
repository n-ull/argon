<?php

namespace Database\Factories\Domain\ProductCatalog\Models;

use Domain\ProductCatalog\Models\Combo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComboFactory extends Factory
{
    protected $model = Combo::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
