<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
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
    public function definition()
    {
        return [
            'name' => null,
            'platoon_id' => Platoon::factory(),
//            'leader_id' => Member::factory(),
        ];
    }
}
