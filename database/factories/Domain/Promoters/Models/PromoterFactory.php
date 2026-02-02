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

    public function withOrganizer(\Domain\OrganizerManagement\Models\Organizer $organizer, array $pivotData = [])
    {
        return $this->afterCreating(function (Promoter $promoter) use ($organizer, $pivotData) {
            $promoter->organizers()->attach($organizer->id, array_merge([
                'commission_type' => 'fixed',
                'commission_value' => 10,
                'enabled' => true,
            ], $pivotData));
        });
    }
}
