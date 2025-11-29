<?php

namespace Database\Factories;

use App\Models\Leave;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    protected $model = Leave::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'reason' => $this->faker->randomElement(array_keys(Leave::$reasons)),
            'end_date' => now()->addDays(30),
            'requester_id' => User::factory(),
            'approver_id' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'approver_id' => User::factory(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'end_date' => now()->subDays(10),
        ]);
    }
}
