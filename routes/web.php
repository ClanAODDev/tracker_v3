<?php

use App\Http\Controllers\Auth\DiscordController;
use App\Http\Controllers\Auth\LoginController;
use Inertia\Inertia;

include_once 'extra/requests.php';
include_once 'extra/awards.php';

Route::get('unauthorized', fn () => Inertia::render('errors/show', ['status' => 403])->toResponse(request())->setStatusCode(403))
    ->name('errors.unauthorized');

if (app()->environment('local')) {
    Route::get('_dev/gallery', fn () => Inertia::render('dev/gallery'))->name('dev.gallery');
}
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('auth/discord', [DiscordController::class, 'redirect'])->name('auth.discord');
Route::get('auth/discord/callback', [DiscordController::class, 'callback'])->name('auth.discord.callback');
Route::get('auth/discord/pending', [DiscordController::class, 'pending'])->name('auth.discord.pending')->middleware('auth');
Route::post('auth/discord/register', [DiscordController::class, 'register'])->name('auth.discord.register')->middleware('auth');
Route::post('auth/discord/application', [DiscordController::class, 'submitApplication'])->name('auth.discord.application')->middleware('auth');

require 'partials/application.php';
require 'partials/tickets.php';
require 'partials/ajax.php';
require 'partials/members.php';
require 'partials/divisions.php';
require 'partials/reports.php';
require 'partials/clan.php';
require 'partials/documentation.php';
