<?php

use Faker\Generator as Faker;

$factory->define(\App\Division::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'handle_id' => function () {
            return factory(App\Handle::class)->create()->id;
        },
        'abbreviation' => $faker->randomLetter . $faker->randomLetter,
        'description' => $faker->sentence,
        'active' => true,
    ];
});
