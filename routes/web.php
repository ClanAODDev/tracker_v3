<?php

include_once('extra/requests.php');

Route::view('unauthorized', 'errors.403')->name('errors.unauthorized');

Auth::routes();
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

include_once('partials/application.php');
include_once('partials/ajax.php');
include_once('partials/members.php');
include_once('partials/divisions.php');
include_once('partials/slack.php');
include_once('partials/reports.php');
include_once('partials/admin.php');
