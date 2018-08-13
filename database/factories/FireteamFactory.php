<?php

$factory->define('App\Fireteam', function (Faker\Generator $faker) {
    return [
        'name' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'owner_id' => App\User::inRandomOrder()->first(),
        'players_needed' => $faker->numberBetween(1, 5),
        'owner_light' => $faker->numberBetween(300, 330),
        'starts_at' => $faker->date(),
        'type' => array_random([
            'nightfall',
            'strikes',
            'trials of the nine',
            'raid',
            'crucible',
            'down for anything'
        ])
    ];
});
