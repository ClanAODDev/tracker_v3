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

$fakeName = (new Faker\Generator())->name;

$factory->define(App\User::class, function (Faker\Generator $faker) use ($fakeName) {
    return [
        'name' => $fakeName,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'role_id' => 4,
        'settings' => ['foo' => 'bar'],
        'developer' => true,
        'member_id' => 31832
    ];
});
