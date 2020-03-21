<?php

use Faker\Generator as Faker;

$factory->define(\App\Platoon::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'leader_id' => function () {
            return create(\App\Member::class)->id;
        },
        'division_id' => function () {
            return create(\App\Division::class)->id;
        }
    ];
});
