<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberAwardFactory extends Factory
{
    protected $model = MemberAward::class;

    public function definition(): array
    {
        return [
            'member_id'    => Member::factory()->create()->clan_id,
            'award_id'     => Award::factory(),
            'requester_id' => Member::factory(),
            'approved'     => false,
            'reason'       => $this->faker->sentence,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'approved' => true,
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'approved' => false,
        ]);
    }
}
