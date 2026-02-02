<?php

use App\Http\Controllers\MemberController;
use App\Http\Controllers\RecruitingController;
use Illuminate\Support\Facades\Route;

Route::controller(MemberController::class)->group(function () {
    Route::get('search/members', 'search')->name('memberSearch');
    Route::post('search-member', 'searchAutoComplete')->name('memberSearchAjax');
});

Route::controller(RecruitingController::class)->group(function () {
    Route::get('division-platoons/{abbreviation}', 'searchPlatoons')->name('divisionPlatoons');
    Route::post('validate-id/{memberId}', 'validateMemberId')->name('validate-id');
    Route::post('validate-name', 'validateMemberName')->name('validate-name');
    Route::post('division-tasks', 'getTasks')->name('divisionTasks');
    Route::post('platoon-squads', 'searchPlatoonForSquads')->name('getPlatoonSquads');
    Route::post('search-division-threads', 'doThreadCheck')->name('divisionThreadCheck');
    Route::get('recruit', 'index')->name('recruiting.initial');
    Route::post('add-member', 'submitRecruitment')->name('recruiting.addMember');
    Route::get('divisions/{division}/recruit/data', 'getDivisionRecruitData')->name('recruiting.divisionData');
    Route::get('divisions/{division}/recruit/pending-discord', 'pendingDiscord')->name('recruiting.pendingDiscord');
});
