<?php

// Home
Breadcrumbs::register('home', function($breadcrumbs)
{
    $breadcrumbs->push('Home', '/home');
});

// Home > Divisions
Breadcrumbs::register('divisions', function($breadcrumbs, $division)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/'.$division->abbreviation);
});

Breadcrumbs::register('platoons', function($breadcrumbs, $division, $platoon)
{
    $breadcrumbs->parent('home');
    $breadcrumbs->push($division->name, '/divisions/'.$division->abbreviation);
    $breadcrumbs->push($platoon->name, '/'.$platoon->id);
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
