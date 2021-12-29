<?php

namespace Database\Factories;

use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberHandle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberHandleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MemberHandle::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'handle_id' => Handle::factory(),
            'member_id' => Member::factory(),
            'value'     => $this->faker->userName,
        ];
    }
}
