<?php

namespace Database\Factories;

use App\Models\ClanSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClanSnapshotFactory extends Factory
{
    protected $model = ClanSnapshot::class;

    public function definition(): array
    {
        return [
            'total_members' => $this->faker->numberBetween(500, 5000),
            'active_divisions' => $this->faker->numberBetween(5, 25),
            'weekly_active_count' => $this->faker->numberBetween(200, 3000),
            'weekly_voice_count' => $this->faker->numberBetween(100, 2000),
            'monthly_recruits' => $this->faker->numberBetween(10, 200),
            'voice_participation' => $this->faker->randomFloat(2, 10, 60),
            'snapshot_date' => now()->toDateString(),
        ];
    }
}
