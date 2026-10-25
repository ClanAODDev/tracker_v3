<?php

namespace Database\Factories;

use App\Models\ReleaseNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleaseNoteFactory extends Factory
{
    protected $model = ReleaseNote::class;

    public function definition(): array
    {
        return [
            'title'        => $this->faker->sentence(4),
            'version'      => null,
            'body'         => $this->faker->paragraph(),
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(['published_at' => now()->addWeek()]);
    }
}
