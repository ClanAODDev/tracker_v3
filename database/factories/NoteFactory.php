<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'body'      => $this->faker->paragraph,
            'member_id' => Member::factory(),
            'author_id' => User::factory(),
            'type'      => $this->faker->randomElement(['misc', 'positive', 'negative']),
        ];
    }

    public function positive(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'positive',
        ]);
    }

    public function negative(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'negative',
        ]);
    }

    public function misc(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'misc',
        ]);
    }

    public function srLdrOnly(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sr_ldr',
        ]);
    }
}
