<?php

Breadcrumbs::for('member', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->slug));

        if ($member->platoon_id !== 0 && $member->platoon) {
            $breadcrumbs->push(
                $member->platoon->name,
                route('platoon', [$division->slug, $member->platoon->id])
            );
        }

        if ($member->squad_id !== 0 && $member->squad) {
            $breadcrumbs->push(
                $member->squad->name ?: 'Untitled',
                route('squad.show', [$division->slug, $member->platoon->id, $member->squad])
            );
        }
    }

    $breadcrumbs->push('View profile');
});

Breadcrumbs::for('member-note', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->slug));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->slug, $member->platoon->id])
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
        $breadcrumbs->push($division->name, route('division', $division->slug));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->slug, $member->platoon->id])
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
        $breadcrumbs->push($division->name, route('division', $division->slug));
    }

    if ($member->platoon_id !== 0) {
        $breadcrumbs->push(
            ucwords($member->platoon->name),
            route('platoon', [$division->slug, $member->platoon->id])
        );
    }

    $breadcrumbs->push(
        $member->name,
        route('member', $member->getUrlParams())
    );

    $breadcrumbs->push('Recruiting History');
});
