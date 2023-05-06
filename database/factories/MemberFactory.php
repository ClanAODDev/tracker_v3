<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Member;
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
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->userName,
            'clan_id' => $this->faker->numberBetween(10000, 99999),
            'rank_id' => Rank::find(rand(1, 10)),
            'position' => 1,
            'division_id' => Division::factory(),
            'join_date' => $this->faker->dateTimeThisDecade,
            'last_activity' => $this->faker->dateTimeThisMonth,
            'last_ts_activity' => $this->faker->dateTimeThisMonth,
            'last_promoted_at' => $this->faker->dateTimeThisYear,
            'allow_pm' => array_rand([0, 1], 1),
            'privacy_flag' => array_rand([0, 1], 1),
        ];
    }

    public function ofTypeMember(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 1,
                'rank_id' => rand(1, 5),
            ];
        });
    }

    public function squadLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 2,
                'rank_id' => rand(6, 8),
            ];
        });
    }

    public function platoonLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 3,
                'rank_id' => rand(7, 9),
            ];
        });
    }

    public function ofTypeCommander(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 6,
                'rank_id' => 9,
            ];
        });
    }

    public function ofTypeExecutiveOfficer(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => 5,
                'rank_id' => 9,
            ];
        });
    }
}
