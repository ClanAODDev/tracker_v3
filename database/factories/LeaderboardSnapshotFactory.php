<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\LeaderboardSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaderboardSnapshotFactory extends Factory
{
    protected $model = LeaderboardSnapshot::class;

    public function definition(): array
    {
        return [
            'division_id'   => Division::factory(),
            'category'      => $this->faker->randomElement(['voice', 'growth', 'recruits']),
            'rank'          => $this->faker->numberBetween(1, 20),
            'value'         => $this->faker->randomFloat(2, 0, 100),
            'previous_rank' => null,
            'rank_change'   => 0,
            'trend_data'    => null,
            'snapshot_date' => now()->toDateString(),
        ];
    }

    public function voice(): static
    {
        return $this->state(['category' => 'voice']);
    }

    public function growth(): static
    {
        return $this->state(['category' => 'growth']);
    }

    public function recruits(): static
    {
        return $this->state(['category' => 'recruits']);
    }

    public function ranked(int $rank): static
    {
        return $this->state(['rank' => $rank]);
    }

    public function withPreviousRank(int $previousRank): static
    {
        return $this->state(fn (array $attributes) => [
            'previous_rank' => $previousRank,
            'rank_change'   => $previousRank - $attributes['rank'],
        ]);
    }
}
