<?php

Breadcrumbs::for('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', route('home'));
});

Breadcrumbs::for('division', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, route('division', $division->slug));
});

Breadcrumbs::for('platoon', function ($breadcrumbs, $division, $platoon) {
    $breadcrumbs->parent('division', $division);
    $breadcrumbs->push($platoon->name);
});

Breadcrumbs::for('squad', function ($breadcrumbs, $division, $platoon, $squad) {
    $breadcrumbs->parent('division', $division);
    $breadcrumbs->push($platoon->name, route('platoon', [$division->slug, $platoon->id]));
    $breadcrumbs->push($squad->name ?: 'Untitled');
});

Breadcrumbs::for('division.reports', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('division', $division);
    $breadcrumbs->push('Reports');
});

$reportPages = [
    'division-census' => 'Census',
    'retention-report' => 'Retention',
    'voice-report' => 'Voice',
    'promotions' => 'Promotions',
];

foreach ($reportPages as $route => $label) {
    Breadcrumbs::for($route, function ($breadcrumbs, $division) use ($label) {
        $breadcrumbs->parent('division.reports', $division);
        $breadcrumbs->push($label);
    });
}

$divisionPages = [
    'squads' => 'Squads',
    'part-timers' => 'Part Timers',
    'statistics' => 'Statistics',
    'create-platoon' => 'Create Platoon',
    'manage-division' => 'Manage Division',
    'division-org-chart' => 'Organization Chart',
    'leaves-of-absence' => 'Leaves of Absence',
    'inactive-members' => 'Inactive Members',
    'members' => 'Members',
    'member-requests' => 'Member Requests',
    'send-private-message' => 'Send Private Message',
    'division-notes' => 'Notes',
];

foreach ($divisionPages as $route => $label) {
    Breadcrumbs::for($route, function ($breadcrumbs, $division) use ($label) {
        $breadcrumbs->parent('division', $division);
        $breadcrumbs->push($label);
    });
}
