<?php

use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::controller(ReportsController::class)->prefix('clan')->group(function () {
    Route::middleware('admin')->group(function () {
        Route::get('division-turnover', 'divisionTurnoverReport')->name('reports.division-turnover');
        Route::get('division-roles', 'divisionUsersWithAccess')->name('reports.division-roles');
    });

    Route::get('no-discord', 'usersWithoutDiscordReport')->name('reports.discord');
    Route::get('outstanding-inactives', 'outstandingMembersReport')->name('reports.outstanding-inactives');
    Route::get('census', 'clanCensusReport')->name('reports.clan-census');
    Route::get('leadership', 'leadership')->name('leadership');
});
