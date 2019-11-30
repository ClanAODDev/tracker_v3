<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\App\Handle::class, function (Faker $faker) {
    return [
        'label' => $faker->words(4, true),
        'type' => Str::slug($faker->words(4, true))
    ];
});
