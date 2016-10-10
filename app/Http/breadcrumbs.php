<?php

use App\Division;
use App\Platoon;

Breadcrumbs::register('home', function ($breadcrumbs) {
    $breadcrumbs->push('Home', '/home');
});

// Home > Division
Breadcrumbs::register('division', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
});

// Home > Division > Squads
Breadcrumbs::register('squads', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push('Squads');
});

// Home > Division > Part-timers
Breadcrumbs::register('part-timers', function ($breadcrumbs, $division) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push('Part-timers');
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


Breadcrumbs::register('user', function ($breadcrumbs, $division, $user) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    $breadcrumbs->push($user->name);
});

// Home > Division > Platoon > Member
Breadcrumbs::register('member', function ($breadcrumbs, $division, $platoon, $member) {
    $breadcrumbs->parent('home');

    if ($division instanceof Division) {
        $breadcrumbs->push($division->name, '/divisions/' . $division->abbreviation);
    }

    if ($platoon instanceof Platoon) {
        $breadcrumbs->push($platoon->name, '/platoons/' . $platoon->id);
    }

    $breadcrumbs->push($member->name);
});




/*
// Home > About
Breadcrumbs::register('about', function($breadcrumbs)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push('About', route('about'));
});

// Home > Blog
Breadcrumbs::register('blog', function($breadcrumbs)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Blog', route('blog'));
});

// Home > Blog > [Category]
Breadcrumbs::register('category', function($breadcrumbs, $category)
{
    $breadcrumbs->parent('blog');
    $breadcrumbs->push($category->title, route('category', $category->id));
});

// Home > Blog > [Category] > [Page]
Breadcrumbs::register('page', function($breadcrumbs, $page)
{
    $breadcrumbs->parent('category', $page->category);
    $breadcrumbs->push($page->title, route('page', $page->id));
});
*/
