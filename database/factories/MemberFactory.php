<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Member;
use App\Models\Position;
use App\Models\Rank;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array:
    {
        return [
            'name' => $this->faker->userName,
            'clan_id' => $this->faker->numberBetween(10000, 99999),
            'rank_id' => Rank::find(rand(1, 10)),
            'position_id' => Position::find(rand(1, 7)),
            'division_id' => Division::factory(),
            'last_activity' => now(),
            'last_promoted_at' => now()->subYear(),
        ];
    }
}
