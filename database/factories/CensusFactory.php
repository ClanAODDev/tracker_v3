<?php

namespace Database\Factories;

use App\Models\Census;
use Illuminate\Database\Eloquent\Factories\Factory;

class CensusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Census::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'division_id'         => \App\Models\Division::factory(),
            'count'               => rand(100, 500),
            'weekly_active_count' => rand(50, 100),
            'weekly_ts_count'     => rand(50, 100),
        ];
    }
}
