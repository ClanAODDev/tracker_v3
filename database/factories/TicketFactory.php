<?php

use Faker\Generator as Faker;

$factory->define(\App\Ticket::class, function (Faker $faker) {
    $states = ['new', 'assigned', 'resolved'];
    $randomState = array_rand($states);

    return [
        'state' => $states[$randomState],
        'type_id' => \App\TicketType::inRandomOrder()->first()->id,
        'caller_id' => \App\User::inRandomOrder()->first()->id,
        'division_id' => \App\Division::inRandomOrder()->active()->get()->first()->id,
        'description' => $faker->paragraph,
        'owner_id' => $states[$randomState] == 'assigned'
            ? \App\User::whereRoleId(5)->inRandomOrder()->first()->id
            : null,
    ];
});
