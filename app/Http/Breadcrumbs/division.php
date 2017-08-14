<?php

registerDivisionSubPages();

Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('index'));
});

// Home > Division
Breadcrumbs::register('division', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->abbreviation));
});

// Home > Division > Platoon
Breadcrumbs::register('platoon', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    $breadcrumbs->push($platoon->name);
});

// Home > Division > Platoon > Squad
Breadcrumbs::register('squad', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->abbreviation));
    $breadcrumbs->push($platoon->name, route('platoon', [$division->abbreviation, $platoon->id]));
    $breadcrumbs->push('All Squads', route('platoonSquads', [$division->abbreviation, $platoon->id]));
    $breadcrumbs->push('Squad');
});

/**
 * Handle static division sub pages
 */
function registerDivisionSubPages()
{
    $divisionStaticSubPages = [
        'squads',
        'part-timers',
        'statistics',
        'create-platoon',
        'division-census',
        'manage-division',
        'division-structure',
        'leaves-of-absence',
        'inactive-members',
        'teamspeak-report',
        'promotions',
        'members',
    ];

    foreach ($divisionStaticSubPages as $page) {
        Breadcrumbs::register($page, function ($breadcrumbs, $division) use ($page) {
            $breadcrumbs->parent('home');
            $breadcrumbs->push($division->name, route('division', $division->abbreviation));
            $breadcrumbs->push(ucwords(
                str_replace('-', ' ', $page)
            ));
        });
    }
}
