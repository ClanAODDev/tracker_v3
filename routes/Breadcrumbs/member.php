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

Breadcrumbs::for('member.context', function ($breadcrumbs, $member, $division) {
    $breadcrumbs->parent('home');

    if ($division) {
        $breadcrumbs->push($division->name, route('division', $division->slug));

        if ($member->platoon_id !== 0 && $member->platoon) {
            $breadcrumbs->push(
                $member->platoon->name,
                route('platoon', [$division->slug, $member->platoon->id])
            );
        }
    }

    $breadcrumbs->push($member->name, route('member', $member->getUrlParams()));
});

$memberPages = [
    'member-note'     => 'Edit Note',
    'member-leave'    => 'Edit Leave',
    'member-recruits' => 'Recruiting History',
];

foreach ($memberPages as $route => $label) {
    Breadcrumbs::for($route, function ($breadcrumbs, $member, $division) use ($label) {
        $breadcrumbs->parent('member.context', $member, $division);
        $breadcrumbs->push($label);
    });
}
