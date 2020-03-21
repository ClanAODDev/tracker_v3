<?php
use Illuminate\Support\Str;

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
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'role_id' => 1,
        'settings' => [],
        'developer' => false,
        'member_id' => function () {
            return factory(\App\Member::class)->create()->clan_id;
        },
        'remember_token' => Str::random(10),
    ];
});

$factory->state(App\User::class, 'admin', function ($faker) {
    return ['developer' => true];
});
