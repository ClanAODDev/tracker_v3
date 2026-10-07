<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Platoon;
use App\Services\Units\LegacyUnitSync;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlatoonFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Platoon::class;

    /**
     * Define the model's default state.
     */
    public function configure(): static
    {
        return $this->afterCreating(fn (Platoon $platoon) => app(LegacyUnitSync::class)->syncPlatoon($platoon->id));
    }

    public function definition(): array
    {
        return [
            'order'       => 100,
            'name'        => $this->faker->colorName,
            'division_id' => Division::factory(),
            //            'leader_id' => Member::factory(),
        ];
    }
}
