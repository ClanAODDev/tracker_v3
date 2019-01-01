<?php

use Faker\Generator as Faker;

$factory->define(\App\Handle::class, function (Faker $faker) {
    return [
        'label' => $faker->words(4, true),
        'type' => str_slug($faker->words(4, true))
    ];
});
