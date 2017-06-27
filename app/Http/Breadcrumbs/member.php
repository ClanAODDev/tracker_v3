<?php

Breadcrumbs::register('member', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push($member->platoon->name, route('platoon', [$division->abbreviation, $member->platoon->id]));
    }

    if ($member->squad_id !== 0) {
        $breadcrumbs->push($member->squad->name,
            route('platoonSquads', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push('View profile');

});


Breadcrumbs::register('member-note', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    if ($member->squad_id !== 0) {
        $breadcrumbs->push($member->squad->name,
            route('platoonSquads', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', [$member->clan_id])
    );

    $breadcrumbs->push('Edit Note');

});


Breadcrumbs::register('member-leave', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    if ($member->squad_id !== 0) {
        $breadcrumbs->push($member->squad->name,
            route('platoonSquads', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', [$member->clan_id])
    );

    $breadcrumbs->push('Edit Leave');

});