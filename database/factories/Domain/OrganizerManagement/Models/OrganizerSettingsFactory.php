<?php

namespace Database\Factories\Domain\OrganizerManagement\Models;

use Domain\OrganizerManagement\Models\OrganizerSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizerSettingsFactory extends Factory
{
    protected $model = OrganizerSettings::class;

    public function definition(): array
    {
        return [
            'raise_money_method' => 'split',
            'is_modo_active' => false,
            'is_mercadopago_active' => false,
        ];
    }
}
