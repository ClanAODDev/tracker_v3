<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Member::class, function (Faker $faker) {
    $memberId = $faker->numberBetween(10000, 99999);
    $division = create(\App\Models\Division::class);
    $platoon = create(\App\Models\Platoon::class, ['division_id' => $division->id]);
    $squad = create(\App\Models\Squad::class, ['platoon_id' => $platoon->id]);

    return [
        'name' => $faker->userName,
        'clan_id' => $memberId,
        'rank_id' => 3,
        'platoon_id' => $platoon->id,
        'squad_id' => $squad->id,
        'position_id' => $faker->numberBetween(1, 7),
        'division_id' => $division->id,
    ];
});
