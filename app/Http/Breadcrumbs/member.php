<?php

use App\Member;

Breadcrumbs::register('member', function ($breadcrumbs, Member $member) {
    $breadcrumbs->parent('home');

    if ($member->primaryDivision) {
        $breadcrumbs->push($member->primaryDivision->name, route('division', $member->primaryDivision->abbreviation));
    }

    if ($member->platoon) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$member->primaryDivision->abbreviation, $member->platoon->id])
        );
    }

    if ($member->squad) {
        $breadcrumbs->push(ucwords($member->squad->name));
    }

});