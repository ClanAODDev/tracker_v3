<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Handle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'slug' => Str::slug($game['name']),
            'handle_id' => Handle::factory(),
            'abbreviation' => strtolower($game['abbreviation']),
            'description' => $this->faker->sentence,
            'forum_app_id' => rand(100, 999),
            'active' => true,
            'settings' => '[]',
        ];
    }

    public function inactive(): DivisionFactory
    {
        return $this->state(function () {
            return [
                'active' => false,
            ];
        });
    }
}
