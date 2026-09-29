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
     */
    public function definition(): array
    {
        $uniqueId = $this->faker->unique()->numberBetween(1000, 9999);
        $name     = 'Test Division ' . $uniqueId;

        return [
            'name'         => $name,
            'slug'         => Str::slug($name),
            'abbreviation' => 'td' . $uniqueId,
            'description'  => $this->faker->sentence,
            'forum_app_id' => rand(100, 999),
            'active'       => true,
            'settings'     => '[]',
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Division $division) {
            if ($division->handles()->doesntExist()) {
                $division->handles()->attach(Handle::factory()->create());
            }
        });
    }

    public function withHandles(Handle ...$handles): static
    {
        return $this->afterCreating(function (Division $division) use ($handles) {
            $division->handles()->sync(
                collect($handles)->mapWithKeys(fn (Handle $handle, int $index) => [$handle->id => ['sort_order' => $index]])->all()
            );
        });
    }

    public function withoutHandles(): static
    {
        return $this->afterCreating(fn (Division $division) => $division->handles()->detach());
    }

    public function inactive(): self
    {
        return $this->state(function () {
            return [
                'active' => false,
            ];
        });
    }
}
