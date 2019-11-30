<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(\App\Handle::class, function (Faker $faker) {
    return [
        'label' => $faker->words(4, true),
        'type' => Str::slug($faker->words(4, true))
    ];
});
