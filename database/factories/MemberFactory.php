<?php

namespace Database\Factories;

use App\Enums\Position;
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
            'rank' => Rank::find(rand(1, 10)),
            'position' => Position::MEMBER,
            'division_id' => Division::factory(),
            'join_date' => $this->faker->dateTimeThisDecade,
            'last_activity' => $this->faker->dateTimeThisMonth,
            'last_ts_activity' => $this->faker->dateTimeThisMonth,
            'last_voice_activity' => $this->faker->dateTimeThisMonth,
            'last_promoted_at' => $this->faker->dateTimeThisYear,
            'allow_pm' => array_rand([0, 1], 1),
            'privacy_flag' => array_rand([0, 1], 1),
        ];
    }

    public function ofTypeMember(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::MEMBER,
                'rank' => rand(\App\Enums\Rank::RECRUIT->value, \App\Enums\Rank::SPECIALIST->value),
            ];
        });
    }

    public function squadLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::SQUAD_LEADER,
                'rank' => rand(\App\Enums\Rank::TRAINER->value, \App\Enums\Rank::CORPORAL->value),
            ];
        });
    }

    public function platoonLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::PLATOON_LEADER,
                'rank' => rand(\App\Enums\Rank::LANCE_CORPORAL->value, \App\Enums\Rank::SERGEANT->value),
            ];
        });
    }

    public function ofTypeCommander(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::COMMANDING_OFFICER,
                'rank' => \App\Enums\Rank::SERGEANT,
            ];
        });
    }

    public function ofTypeExecutiveOfficer(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::EXECUTIVE_OFFICER,
                'rank' => \App\Enums\Rank::SERGEANT,
            ];
        });
    }
}
