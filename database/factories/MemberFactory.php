<?php

use Faker\Generator as Faker;

$factory->define(\App\Member::class, function (Faker $faker) {
    $memberId = $faker->numberBetween(10000, 99999);
    return [
        'name' => $faker->userName,
        'clan_id' => $memberId,
        'rank_id' => 3,
        'platoon_id' => function () {
            return factory(App\Platoon::class)->create()->id;
        },
        'squad_id' => function () {
            return factory(App\Squad::class)->create()->id;
        },
        'position_id' => $faker->numberBetween(1,7),
        'division_id' => function () {
            return factory(App\Division::class)->create()->id;
        },
    ];
});
