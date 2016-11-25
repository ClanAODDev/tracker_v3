<?php

registerDivisionSubPages();

Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', '/home');
});

// Home > Division
Breadcrumbs::register('division', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
});

// Home > Division > Platoon
Breadcrumbs::register('platoon', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push($platoon->name);
});

// Home > Division > Platoon > Squad
Breadcrumbs::register('squad', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push($platoon->name, '/platoons/' . $platoon->id);
    $breadcrumbs->push('Squad');
});

/**
 * Handle static division sub pages
 */
function registerDivisionSubPages() {

    $divisionStaticSubPages = [
        'squads',
        'part-timers',
        'statistics',
    ];

    foreach($divisionStaticSubPages as $page) {
        Breadcrumbs::register($page, function ($breadcrumbs, $division) use ($page) {
            $breadcrumbs->parent('home');
            $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
            $breadcrumbs->push(ucwords($page));
        });
    }
}