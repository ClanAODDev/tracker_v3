<?php

registerDivisionSubPages();

Breadcrumbs::for('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('home'));
});

// Home > Division
Breadcrumbs::for('division', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->slug));
});

// Home > Division > Platoon
Breadcrumbs::for('platoon', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->slug));
    $breadcrumbs->push($platoon->name);
});

// Home > Division > Platoon > Squad
Breadcrumbs::for('squad', function ($breadcrumbs, $division, $platoon, $squad) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->slug));
    $breadcrumbs->push($platoon->name, route('platoon', [$division->slug, $platoon->id]));
    $breadcrumbs->push($squad->name ?: 'Untitled');
});

/**
 * Handle static division sub pages.
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
        'retention-report',
        'voice-report',
        'ingame-report',
        'promotions',
        'members',
        'member-requests',
        'send-private-message',
    ];

    foreach ($divisionStaticSubPages as $page) {
        Breadcrumbs::for($page, function ($breadcrumbs, $division) use ($page) {
            $breadcrumbs->parent('home');
            $breadcrumbs->push($division->name, route('division', $division->slug));
            $breadcrumbs->push(ucwords(
                str_replace('-', ' ', $page)
            ));
        });
    }
}
