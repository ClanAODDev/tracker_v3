<?php

namespace Database\Factories;

use App\Enums\Rank;
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
                'role' => Role::ADMIN,
                'developer' => true,
                'member_id' => Member::factory([
                    'rank' => Rank::MASTER_SERGEANT,
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
                    'rank' => Rank::LANCE_CORPORAL,
                ]),
            ];
        });
    }

    public function pending(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'member_id' => null,
                'discord_id' => $this->faker->numerify('#########'),
                'discord_username' => $this->faker->userName,
                'date_of_birth' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'forum_password' => $this->faker->password(8, 16),
            ];
        });
    }
}
