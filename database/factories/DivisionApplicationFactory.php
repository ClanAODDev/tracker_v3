<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionApplicationFactory extends Factory
{
    protected $model = DivisionApplication::class;

    public function definition(): array
    {
        return [
            'division_id' => Division::factory(),
            'user_id'     => User::factory(),
            'responses'   => [],
        ];
    }

    public function withResponses(array $responses): self
    {
        return $this->state(fn (array $attributes) => [
            'responses' => $responses,
        ]);
    }

    public function forDivision(Division $division): self
    {
        return $this->state(fn (array $attributes) => [
            'division_id' => $division->id,
        ]);
    }
}
