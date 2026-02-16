<?php

namespace Database\Factories;

use App\Enums\Rank;
use App\Models\Member;
use App\Models\RankAction;
use Illuminate\Database\Eloquent\Factories\Factory;

class RankActionFactory extends Factory
{
    protected $model = RankAction::class;

    public function definition(): array
    {
        return [
            'member_id'    => Member::factory(),
            'requester_id' => Member::factory(),
            'rank'         => Rank::from(rand(2, 10)),
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'approver_id' => Member::factory(),
            'approved_at' => now(),
        ]);
    }

    public function accepted(): self
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now(),
        ]);
    }

    public function declined(): self
    {
        return $this->state(fn (array $attributes) => [
            'declined_at' => now(),
        ]);
    }

    public function denied(): self
    {
        return $this->state(fn (array $attributes) => [
            'denied_at'   => now(),
            'deny_reason' => $this->faker->sentence,
        ]);
    }

    public function awarded(): self
    {
        return $this->state(fn (array $attributes) => [
            'awarded_at' => now(),
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'accepted_at' => null,
            'declined_at' => null,
            'denied_at'   => null,
        ]);
    }

    public function complete(): self
    {
        return $this->approved()->accepted()->awarded();
    }
}
