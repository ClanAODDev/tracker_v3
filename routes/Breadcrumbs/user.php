<?php

// Home > Division > Platoon > Member
Breadcrumbs::for('user', function ($breadcrumbs, $division, $user) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    $breadcrumbs->push($user->name);
});
