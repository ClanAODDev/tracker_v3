<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\Division;
use Illuminate\Database\Eloquent\Factories\Factory;

class AwardFactory extends Factory
{
    protected $model = Award::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->words(3, true),
            'description'   => $this->faker->sentence,
            'division_id'   => Division::factory(),
            'active'        => true,
            'allow_request' => true,
            'repeatable'    => false,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function repeatable(): self
    {
        return $this->state(fn (array $attributes) => [
            'repeatable' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function notRequestable(): self
    {
        return $this->state(fn (array $attributes) => [
            'allow_request' => false,
        ]);
    }

    public function global(): self
    {
        return $this->state(fn (array $attributes) => [
            'division_id' => null,
        ]);
    }
}
