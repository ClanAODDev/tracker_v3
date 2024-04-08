<?php

// Home > Division > Platoon > Member
Breadcrumbs::for('user', function ($breadcrumbs, $division, $user) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->slug));
    $breadcrumbs->push($user->name);
});
