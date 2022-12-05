<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Platoon::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'leader_id' => function () {
            return create(\App\Models\Member::class)->id;
        },
        'division_id' => function () {
            return create(\App\Models\Division::class)->id;
        },
    ];
});
