<?php

Breadcrumbs::for('member', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));

        if (0 !== $member->platoon_id && $member->platoon) {
            $breadcrumbs->push(
                $member->platoon->name,
                route('platoon', [$division->abbreviation, $member->platoon->id])
            );
        }

        if (0 !== $member->squad_id && $member->squad) {
            $breadcrumbs->push(
                $member->squad->name ?: 'Untitled',
                route('squad.show', [$division->abbreviation, $member->platoon->id, $member->squad])
            );
        }
    }

    $breadcrumbs->push('View profile');
});

Breadcrumbs::for('member-note', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if (0 !== $member->platoon_id) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', $member->getUrlParams())
    );

    $breadcrumbs->push('Edit Note');
});

Breadcrumbs::for('member-leave', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if (0 !== $member->platoon_id) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', $member->getUrlParams())
    );

    $breadcrumbs->push('Edit Leave');
});

Breadcrumbs::for('member-recruits', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    }

    if (0 !== $member->platoon_id) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->abbreviation, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', $member->getUrlParams())
    );

    $breadcrumbs->push('Recruiting History');
});
