<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\HelpController;
use Illuminate\Support\Facades\Route;

Route::get('changelog', [AppController::class, 'changelog'])->name('changelog');

Route::prefix('help/docs')->group(function () {
    Route::get('/', [HelpController::class, 'index'])->name('help');
    Route::view('division-structures', 'help.division-structures')->name('divisionStructures');
    Route::view('member-awards', 'help.member-awards')->name('help.member-awards');
    Route::view('managing-rank', 'help.managing-rank')->name('help.managing-rank');

    Route::middleware('admin')->prefix('admin')->name('help.admin.')->group(function () {
        Route::view('/', 'help.admin.index')->name('home');
        Route::view('division-checklist', 'help.admin.division-checklist')->name('division-checklist');
        Route::view('sink', 'help.admin.sink')->name('sink');
    });
});
