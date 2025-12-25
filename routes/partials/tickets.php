<?php

use Illuminate\Support\Facades\Route;

Route::prefix('help/tickets')->name('help.tickets.')->middleware('auth')->group(function () {
    Route::view('/', 'help.tickets.widget')->name('widget');

    Route::get('setup', fn () => redirect()->route('help.tickets.widget'));
    Route::get('create', fn () => redirect()->route('help.tickets.widget'));
    Route::get('{ticket}', fn () => redirect()->route('help.tickets.widget'));
});
