<?php

use App\Http\Controllers\InactiveMemberController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SquadController;
use Illuminate\Support\Facades\Route;

Route::prefix('members')->group(function () {
    Route::controller(PromotionController::class)->prefix('{member}/promotion/{action}')->name('promotion.')->group(function () {
        Route::get('/', 'confirm')->name('confirm');
        Route::post('/', 'accept')->name('accept');
        Route::post('decline', 'decline')->name('decline');
    });

    Route::controller(MemberController::class)->group(function () {
        Route::get('{member}/confirm-reset', 'confirmUnassign')->name('member.confirm-reset');
        Route::post('{member}/unassign', 'unassignMember')->name('member.unassign');
        Route::post('{member}/assign-platoon', 'assignPlatoon')->name('member.assign-platoon');
        Route::post('search/{name}', 'search');
        Route::get('{member}-{slug?}', 'show')->name('member');
    });

    Route::post('assign-squad', [SquadController::class, 'assignMember']);

    Route::controller(LeaveController::class)->group(function () {
        Route::get('{member}/leave/{leave}/edit', 'edit')->name('leave.edit');
        Route::match(['put', 'patch'], '{member}/leave', 'update')->name('leave.update');
        Route::delete('{member}/leave/{leave}', 'delete')->name('leave.delete');
    });

    Route::controller(NoteController::class)->prefix('{member}/notes')->group(function () {
        Route::post('/', 'store')->name('storeNote');
        Route::get('{note}/edit', 'edit')->name('editNote');
        Route::match(['post', 'patch'], '{note}', 'update')->name('updateNote');
        Route::delete('{note}', 'delete')->name('deleteNote');
    });
});

Route::controller(InactiveMemberController::class)->prefix('inactive-members')->group(function () {
    Route::get('{member}/flag-inactive', 'create')->name('member.flag-inactive');
    Route::get('{member}/unflag-inactive', 'destroy')->name('member.unflag-inactive');
    Route::delete('{member}', 'removeMember')->name('member.drop-for-inactivity');
});
