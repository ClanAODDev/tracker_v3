<?php

/**
 * ajax endpoints.
 */
Route::get('search/members/{name?}', 'MemberController@search')->name('memberSearch');
Route::get('division-platoons/{abbreviation}', 'RecruitingController@searchPlatoons')->name('divisionPlatoons');
Route::post('validate-id/{memberId}', 'RecruitingController@validateMemberId')
    ->name('validate-id');
Route::post('validate-name', 'RecruitingController@validateMemberName')->name('validate-name');
Route::post('division-tasks', 'RecruitingController@getTasks')->name('divisionTasks');
Route::post('search-member', 'MemberController@searchAutoComplete')->name('memberSearchAjax');
Route::post('platoon-squads', 'RecruitingController@searchPlatoonForSquads')->name('getPlatoonSquads');
Route::post('search-division-threads', 'RecruitingController@doThreadCheck')->name('divisionThreadCheck');
Route::post('update-position', 'MemberController@updatePosition')->name('member.position.update');
Route::post('update-handles', 'MemberController@updateHandles')->name('updateMemberHandles');
Route::get('recruit', 'RecruitingController@index')->name('recruiting.initial');
Route::post('add-member', 'RecruitingController@submitRecruitment')->name('recruiting.addMember');
