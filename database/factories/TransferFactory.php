<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'division_id' => Division::factory(),
            'approved_at' => null,
            'hold_placed_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'hold_placed_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
            'hold_placed_at' => null,
        ]);
    }
}
