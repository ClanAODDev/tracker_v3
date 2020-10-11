<?php

namespace Database\Factories;

use App\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $states = ['new', 'assigned', 'resolved'];
        $randomState = array_rand($states);

        return [
            'state' => $states[$randomState],
            'type_id' => \App\TicketType::inRandomOrder()->first()->id,
            'caller_id' => \App\User::inRandomOrder()->first()->id,
            'division_id' => \App\Division::inRandomOrder()->active()->get()->first()->id,
            'description' => $this->faker->paragraph,
            'owner_id' => $states[$randomState] == 'assigned'
                ? \App\User::whereRoleId(5)->inRandomOrder()->first()->id
                : null,
        ];
    }
}
