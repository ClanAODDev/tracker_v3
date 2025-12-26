<?php

namespace Database\Factories;

use App\Enums\TagVisibility;
use App\Models\Division;
use App\Models\DivisionTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionTagFactory extends Factory
{
    protected $model = DivisionTag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'division_id' => Division::factory(),
            'visibility' => TagVisibility::PUBLIC,
        ];
    }

    public function global(): self
    {
        return $this->state(fn (array $attributes) => [
            'division_id' => null,
        ]);
    }

    public function public(): self
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => TagVisibility::PUBLIC,
        ]);
    }

    public function officersOnly(): self
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => TagVisibility::OFFICERS,
        ]);
    }

    public function seniorLeadersOnly(): self
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => TagVisibility::SENIOR_LEADERS,
        ]);
    }
}
