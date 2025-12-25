<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'subject_type' => Member::class,
            'subject_id' => Member::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function forSubject($subject): self
    {
        return $this->state(fn (array $attributes) => [
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
        ]);
    }
}
