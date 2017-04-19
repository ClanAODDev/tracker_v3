<?php

use App\Division;
use App\Member;

Breadcrumbs::register('member', function ($breadcrumbs, Member $member, Division $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if ($member->platoon) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    if ($member->squad) {
        $breadcrumbs->push(ucwords($member->squad->name));
    }

    $breadcrumbs->push('View profile');

});