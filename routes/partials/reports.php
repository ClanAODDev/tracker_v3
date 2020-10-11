<?php

Route::group(['prefix' => 'reports'], function () {
    Route::middleware('admin')->get('division-turnover', 'ReportsController@divisionTurnoverReport')
        ->name('reports.division-turnover');

    Route::middleware('admin')->get('division-roles', 'ReportsController@divisionUsersWithAccess')
        ->name('reports.division-roles');

    Route::get('no-discord', 'ReportsController@usersWithoutDiscordReport')
        ->name('reports.discord');
    Route::get(
        'outstanding-inactives',
        'ReportsController@outstandingMembersReport'
    )->name('reports.outstanding-inactives');
    Route::get('/clan-census', 'ReportsController@clanCensusReport')->name('reports.clan-census');
    Route::get('/clan-ts-report', 'ReportsController@clanTsReport')->name('reports.clan-ts-report');

    // other reporty things
    Route::get('leadership', 'ReportsController@leadership')->name('leadership');
});
