<?php

namespace Database\Factories;

use App\Models\Handle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HandleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Handle::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $handles = [
            'origin', 'ea', 'blizzard', 'steam', 'activision', 'riot', 'battle tag', 'wows', 'xbox-live',
        ];

        $label = $handles[array_rand($handles)];

        return [
            'label' => $label,
            'type' => Str::slug($label),
        ];
    }
}
