<?php

use App\Http\Controllers\API\DivisionApplicationApiController;
use App\Http\Controllers\API\TicketApiController;
use App\Http\Controllers\API\v1\ClanController;
use App\Http\Controllers\API\v1\DivisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function () {
    Route::controller(ClanController::class)->middleware('abilities:clan:read')->group(function () {
        Route::get('ts-count', 'teamspeakPopulationCount')->name('ts_population');
        Route::get('discord-count', 'discordPopulationCount')->name('discord_population');
        Route::get('stream-events', 'streamEvents')->name('stream_events');
    });

    Route::controller(DivisionController::class)->prefix('divisions')->name('divisions.')->group(function () {
        Route::middleware('abilities:division:read')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('{slug}', 'show')->name('show');
        });

        Route::post('{slug}', 'update')->middleware('abilities:division:write')->name('update');
    });
});

Route::prefix('divisions/{division}/applications')->middleware(['web', 'auth'])->name('division-applications.')->group(function () {
    Route::get('/', [DivisionApplicationApiController::class, 'index'])->name('index');
    Route::get('/{application}', [DivisionApplicationApiController::class, 'show'])->name('show');
    Route::delete('/{application}', [DivisionApplicationApiController::class, 'destroy'])->name('destroy');
    Route::post('/{application}/comments', [DivisionApplicationApiController::class, 'addComment'])->name('comments.store');
    Route::delete('/{application}/comments/{comment}', [DivisionApplicationApiController::class, 'deleteComment'])->name('comments.destroy');
});

Route::prefix('tickets')->middleware(['web', 'auth'])->name('tickets.')->group(function () {
    Route::get('/', [TicketApiController::class, 'index'])->name('index');
    Route::get('/types', [TicketApiController::class, 'types'])->name('types');
    Route::get('/workable', [TicketApiController::class, 'workableIndex'])->name('workable');
    Route::post('/', [TicketApiController::class, 'store'])->name('store');
    Route::get('/{ticket}', [TicketApiController::class, 'show'])->name('show');
    Route::post('/{ticket}/comments', [TicketApiController::class, 'addComment'])->name('comments.store');
    Route::post('/{ticket}/own', [TicketApiController::class, 'own'])->name('own');
    Route::post('/{ticket}/resolve', [TicketApiController::class, 'resolve'])->name('resolve');
    Route::post('/{ticket}/reject', [TicketApiController::class, 'reject'])->name('reject');
    Route::post('/{ticket}/reopen', [TicketApiController::class, 'reopen'])->name('reopen');
});
