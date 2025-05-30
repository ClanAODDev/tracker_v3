<?php

include_once 'extra/requests.php';
include_once 'extra/awards.php';

Route::view('unauthorized', 'errors.403')->name('errors.unauthorized');
Auth::routes();
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

require 'partials/application.php';
require 'partials/tickets.php';
require 'partials/ajax.php';
require 'partials/members.php';
require 'partials/divisions.php';
require 'partials/reports.php';
require 'partials/clan.php';
require 'partials/website.php';
require 'partials/documentation.php';
