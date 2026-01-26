<?php

namespace Database\Factories\Domain\Promoters\Models;

use App\Models\User;
use Domain\Promoters\Models\Promoter;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromoterFactory extends Factory
{
    protected $model = Promoter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'referral_code' => \Illuminate\Support\Str::random(6),
            'enabled' => true,
        ];
    }
}
