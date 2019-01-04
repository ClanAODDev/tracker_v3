<?php

use Faker\Generator as Faker;

$factory->define(\App\Division::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'handle_id' => function () {
            return create('App\Handle')->id;
        },
        'abbreviation' => $faker->word,
        'description' => $faker->sentence,
        'active' => true,
    ];
});
