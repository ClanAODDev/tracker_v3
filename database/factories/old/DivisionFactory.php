<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Division::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'handle_id' => function () {
            return create(\App\Models\Handle::class)->id;
        },
        'abbreviation' => $faker->word,
        'description' => $faker->sentence,
        'active' => true,
    ];
});
