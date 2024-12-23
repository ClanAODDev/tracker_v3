<?php

// Home > Awards > Award
Breadcrumbs::for('awards.show', function ($breadcrumbs, $award) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Awards', route('awards.index'));
    $breadcrumbs->push($award->name);
});

Breadcrumbs::for('awards.index', function ($breadcrumbs) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Awards', route('awards.index'));

    if (request('division')) {
        $breadcrumbs->push(ucwords(str_replace('-', ' ', request('division'))));
    }
});
