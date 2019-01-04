<?php

use Faker\Generator as Faker;

$factory->define(\App\Platoon::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'leader_id' => function () {
            return create('App\Member')->id;
        },
        'division_id' => function () {
            return create('App\Division')->id;
        }
    ];
});
