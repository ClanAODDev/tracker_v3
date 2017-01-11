<?php

use App\Division;
use App\Platoon;

Breadcrumbs::register('member', function ($breadcrumbs, $division, $platoon, $member) {
    $breadcrumbs->parent('home');

    if ($division instanceof Division) {
        $breadcrumbs->push($division->name, route('division',  $division->abbreviation));
    }

    if ($platoon instanceof Platoon) {
        $breadcrumbs->push($platoon->name, route('division', [$division->abbreviation, $platoon->id]));
    }

    $breadcrumbs->push($member->name);
});
