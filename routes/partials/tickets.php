<?php

use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::prefix('help/tickets')->name('help.tickets.')->middleware('auth')->group(function () {
    Route::view('/', 'help.tickets.widget')->name('widget');

    Route::get('setup', fn () => redirect()->route('help.tickets.widget'));
    Route::get('create', fn () => view('help.tickets.widget', ['initialView' => 'select-type']))->name('create');
    Route::get('{ticket}', fn (Ticket $ticket) => view('help.tickets.widget', ['initialTicketId' => $ticket->id]))->name('show');
});
