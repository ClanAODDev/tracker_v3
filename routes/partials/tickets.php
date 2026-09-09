<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('help/tickets')->name('help.tickets.')->middleware('auth')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('widget');
    Route::get('setup', fn () => redirect()->route('help.tickets.widget'));
    Route::get('create', [TicketController::class, 'create'])->name('create');
    Route::get('{ticket}', [TicketController::class, 'show'])->name('show');
});
