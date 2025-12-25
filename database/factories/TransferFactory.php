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
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
        ]);
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => null,
        ]);
    }
}
