<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

Route::get('changelog', [AppController::class, 'changelog'])->name('changelog');

Route::controller(HelpController::class)->prefix('help/docs')->group(function () {
    Route::get('/', 'index')->name('help');
    Route::get('member-awards', 'memberAwards')->name('help.member-awards');
    Route::get('managing-rank', 'managingRank')->name('help.managing-rank');
    Route::get('recruiting', 'recruiting')->name('help.recruiting');

    Route::middleware('admin')->prefix('admin')->name('help.admin.')->group(function () {
        Route::get('/', 'adminContributing')->name('home');
        Route::get('division-checklist', 'adminChecklist')->name('division-checklist');
        Route::get('sink', 'adminSink')->name('sink');
    });
});
