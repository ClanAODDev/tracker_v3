<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Handle;
use Illuminate\Database\Eloquent\Factories\Factory;

class DivisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Division::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $game = $this->faker->game;

        return [
            'name' => $game['name'],
            'handle_id' => Handle::factory(),
            'abbreviation' => strtolower($game['abbreviation']),
            'description' => $this->faker->sentence,
            'active' => true,
            'settings' => '[]',
        ];
    }
}
