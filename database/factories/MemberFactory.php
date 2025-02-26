<?php

namespace Database\Factories;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Division;
use App\Models\Member;
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
            'clan_id' => $this->faker->numberBetween(1000, 999999),
            'rank' => Rank::from(rand(1, 10)),
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
                'rank' => rand(Rank::RECRUIT->value, Rank::SPECIALIST->value),
            ];
        });
    }

    public function ofTypeSquadLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::SQUAD_LEADER,
                'rank' => rand(Rank::TRAINER->value, Rank::CORPORAL->value),
            ];
        });
    }

    public function ofTypePlatoonLeader(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::PLATOON_LEADER,
                'rank' => rand(Rank::LANCE_CORPORAL->value, Rank::SERGEANT->value),
            ];
        });
    }

    public function ofTypeCommander(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::COMMANDING_OFFICER,
                'rank' => Rank::SERGEANT,
            ];
        });
    }

    public function ofTypeExecutiveOfficer(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'position' => Position::EXECUTIVE_OFFICER,
                'rank' => Rank::SERGEANT,
            ];
        });
    }
}
