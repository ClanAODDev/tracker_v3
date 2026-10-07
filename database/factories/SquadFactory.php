<?php

namespace Database\Factories;

use App\Models\Platoon;
use App\Models\Squad;
use App\Services\Units\LegacyUnitSync;
use Illuminate\Database\Eloquent\Factories\Factory;

class SquadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Squad::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function configure(): static
    {
        return $this->afterCreating(fn (Squad $squad) => app(LegacyUnitSync::class)->syncSquad($squad->id));
    }

    public function definition()
    {
        return [
            'name'       => $this->faker->streetName,
            'platoon_id' => Platoon::factory(),
        ];
    }
}
