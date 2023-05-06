<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = $this->faker->userName;

        return [
            'name' => $name,
            'email' => $this->faker->email,
            'role' => Role::MEMBER,
            'member_id' => Member::factory([
                'name' => $name,
            ]),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => Role::ADMINISTRATOR,
                'developer' => true,
                'member_id' => Member::factory([
                    'rank_id' => 11,
                ]),
            ];
        });
    }

    /**
     * Indicate that the user is an officer.
     */
    public function officer(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => Role::OFFICER,
                'member_id' => Member::factory([
                    'rank_id' => 7,
                ]),
            ];
        });
    }
}
