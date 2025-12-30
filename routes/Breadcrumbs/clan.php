<?php

// Home > Awards > Award
Breadcrumbs::for('awards.show', function ($breadcrumbs, $award) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Achievements', route('awards.index'));

    if (isset($award->division_id)) {
        $breadcrumbs->push(
            $award->division->name,
            route('awards.index') . "?division={$award->division->slug}"
        );
    }

    $breadcrumbs->push($award->name);
});

Breadcrumbs::for('awards.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Achievements', route('awards.index'));

    if (request('division')) {
        $breadcrumbs->push(ucwords(str_replace('-', ' ', request('division'))));
    }
});

Breadcrumbs::for('awards.tiered', function ($breadcrumbs, $group) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Achievements', route('awards.index'));
    $breadcrumbs->push($group['name']);
});
