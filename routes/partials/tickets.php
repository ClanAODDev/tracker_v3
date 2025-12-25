<?php

use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('help/tickets')->name('help.tickets.')->group(function () {
    Route::controller(TicketController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('setup', 'setup')->name('setup');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'store')->name('delete');
        Route::get('{ticket}', 'show')->name('show');
        Route::patch('{ticket}/self-assign', 'selfAssign')->name('self-assign');
        Route::patch('{ticket}/assign-to', 'assignTo')->name('assign-to');
        Route::patch('{ticket}/resolve', 'resolve')->name('resolve');
        Route::patch('{ticket}/reopen', 'reopen')->name('reopen');
        Route::patch('{ticket}/reject', 'reject')->name('reject');
    });

    Route::controller(TicketCommentController::class)->prefix('{ticket}/comments')->name('comments.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'delete')->name('delete');
    });
});
