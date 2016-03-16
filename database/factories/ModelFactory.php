<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt('test'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Member::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->userName,
        'clan_id' => $faker->randomNumber(6),
        'rank_id' => $faker->numberBetween(1, 14),
        'platoon_id' => $faker->numberBetween(1, 3),
        'position_id' => $faker->numberBetween(1, 8),
        'squad_id' => $faker->numberBetween(1, 3),
        'join_date' => $faker->dateTime('now'),
        'last_forum_login' => $faker->dateTime('now'),
    ];
});

$factory->define(App\Platoon::class, function (Faker\Generator $faker) {
    return [
        'order' => $faker->numberBetween(1, 3),
        'name' => $faker->name,
        'division_id' => 1,
        'platoon_leader_id' => $faker->randomNumber(5)
    ];
});

$factory->define(App\Squad::class, function (Faker\Generator $faker) {
    return [
        'platoon_id' => $faker->numberBetween(1, 3),
        'squad_leader_id' => $faker->randomNumber(5)
    ];
});
