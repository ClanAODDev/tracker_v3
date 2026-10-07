<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Unit $unit) {
            $unit->update(['path' => ($unit->parent?->path ?? '/') . $unit->id . '/']);
        });
    }

    public function definition(): array
    {
        return [
            'name'        => $this->faker->colorName,
            'order'       => 100,
            'depth'       => 1,
            'gen_pop'     => false,
            'division_id' => Division::factory(),
        ];
    }

    public function childOf(Unit $parent): static
    {
        return $this->state(fn () => [
            'parent_id'   => $parent->id,
            'division_id' => $parent->division_id,
            'depth'       => $parent->depth + 1,
            'order'       => 0,
        ]);
    }
}
