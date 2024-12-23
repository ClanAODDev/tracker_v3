<?php

// Home > Awards > Award
Breadcrumbs::for('awards', function ($breadcrumbs, $award) {
    $breadcrumbs->parent('home');
    $breadcrumbs->push('Awards', route('awards.index'));
    $breadcrumbs->push($award->name);
});
