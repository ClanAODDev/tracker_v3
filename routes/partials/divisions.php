<?php

use App\Http\Controllers\BulkMoveController;
use App\Http\Controllers\BulkTagController;
use App\Http\Controllers\Division\ReportController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\DivisionNoteController;
use App\Http\Controllers\DivisionOrgChartController;
use App\Http\Controllers\InactiveMemberController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PlatoonController;
use App\Http\Controllers\PmController;
use App\Http\Controllers\RecruitingController;
use App\Http\Controllers\SquadController;
use Illuminate\Support\Facades\Route;

Route::prefix('divisions/{division}')->group(function () {
    Route::get('/', [DivisionController::class, 'show'])->name('division');
    Route::get('members', [DivisionController::class, 'members'])->name('division.members');
    Route::get('notes', [DivisionNoteController::class, 'index'])->name('division.notes');
    Route::get('unassigned-to-squad', [DivisionController::class, 'unassignedToSquad'])->name('division.unassigned-to-squad');

    Route::prefix('part-timers')->group(function () {
        Route::get('/', [DivisionController::class, 'partTime'])->name('partTimers');
        Route::post('/', [DivisionController::class, 'addPartTimer'])->name('addPartTimer');
        Route::get('{member}', [DivisionController::class, 'assignPartTime'])->name('assignPartTimer');
        Route::get('{member}/remove', [DivisionController::class, 'removePartTime'])->name('removePartTimer');
    });

    Route::controller(LeaveController::class)->prefix('leave')->group(function () {
        Route::get('/', 'index')->name('leave.index');
        Route::post('/', 'store')->name('leave.store');
    });

    Route::controller(DivisionOrgChartController::class)->prefix('structure')->group(function () {
        Route::get('/', 'show')->name('division.structure');
        Route::get('data', 'data')->name('division.structure.data');
    });

    Route::controller(InactiveMemberController::class)->group(function () {
        Route::get('inactive-members/{platoon?}', 'index')->name('division.inactive-members');
        Route::get('inactive-members-ts/{platoon?}', 'index')->name('division.inactive-members-ts');
        Route::get('inactive-ts-forums/{platoon?}', 'index')->name('division.inactive-ts-forums');
    });

    Route::get('applications', [DivisionController::class, 'applications'])->name('division.applications');
    Route::get('recruit/form', [RecruitingController::class, 'form'])->name('recruiting.form');

    Route::controller(ReportController::class)->group(function () {
        Route::get('voice-report', 'voiceReport')->name('division.voice-report');
        Route::get('retention', 'retentionReport')->name('division.retention-report');
        Route::get('census', 'censusReport')->name('division.census');
        Route::get('promotions/{month?}/{year?}', 'promotionsReport')->middleware('auth')->name('division.promotions');
    });

    Route::prefix('platoons')->group(function () {
        Route::get('{platoon}', [PlatoonController::class, 'show'])->name('platoon');
        Route::get('{platoon}/manage-assignments', [PlatoonController::class, 'manageSquads'])->name('platoon.manage-squads');
        Route::get('{platoon}/squads/{squad}', [SquadController::class, 'show'])->name('squad.show');
    });

    Route::post('private-message', [PmController::class, 'create'])->name('private-message.create');

    Route::controller(BulkTagController::class)->group(function () {
        Route::post('bulk-tags', 'create')->name('bulk-tags.create');
        Route::post('bulk-tags/store', 'store')->name('bulk-tags.store');
        Route::post('bulk-tags/create-tag', 'createDivisionTag')->name('bulk-tags.create-tag');

        Route::prefix('member-tags/{member}')->name('member-tags.')->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::get('json', 'getTags')->name('get');
            Route::post('add', 'addTag')->name('add');
            Route::post('remove', 'removeTag')->name('remove');
            Route::post('create', 'createTag')->name('create');
        });
    });

    Route::controller(BulkMoveController::class)->prefix('bulk-transfer')->name('bulk-transfer.')->group(function () {
        Route::get('platoons', 'getPlatoons')->name('platoons');
        Route::post('/', 'store')->name('store');
    });

    Route::post('bulk-reminder', [MemberController::class, 'bulkReminder'])->name('bulk-reminder.store');
});
