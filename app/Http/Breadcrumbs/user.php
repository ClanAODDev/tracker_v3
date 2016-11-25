<?php

// Home > Division > Platoon > Member
Breadcrumbs::register('user', function ($breadcrumbs, $division, $user) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push($user->name);
});