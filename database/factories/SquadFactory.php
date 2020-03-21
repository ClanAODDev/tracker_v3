<?php

use Faker\Generator as Faker;

$factory->define(\App\Squad::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'platoon_id' => function () {
            return create(\App\Platoon::class)->id;
        }
    ];
});
