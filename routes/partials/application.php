<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\Bot\BotCommandController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\MemberTransferController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TrainingController;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppController::class, 'index'])->name('index');
Route::get('home', [AppController::class, 'index'])->name('home');

Route::controller(ImpersonationController::class)->group(function () {
    Route::get('impersonate/{user}', 'impersonate')->name('impersonate');
    Route::get('impersonate-end', 'endImpersonation')->name('end-impersonation');
    Route::get('impersonate-role/{role}', 'impersonateRole')->name('impersonate-role');
    Route::get('impersonate-role-end', 'endRoleImpersonation')->name('end-role-impersonation');
});

Route::controller(TrainingController::class)->prefix('training')->name('training.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('sgt', 'sgtTraining')->name('sgt');
    Route::get('{slug}', 'show')->name('show');
    Route::post('/', 'update')->name('update');
});

Route::controller(DeveloperController::class)->prefix('developers')->name('developer')->group(function () {
    Route::get('/', 'index');
    Route::post('tokens', 'generateToken')->name('.token.store');
    Route::delete('tokens', 'destroyToken')->name('.token.delete');
});

Route::prefix('primary-nav')->group(function () {
    Route::get('collapse', fn () => session(['primary_nav_collapsed' => true]));
    Route::get('decollapse', fn () => session(['primary_nav_collapsed' => false]));
});

Route::middleware('auth')->get('session/keep-alive', function () {
    return response()->json([
        'expiresAt' => now()->addMinutes(config('session.lifetime'))->timestamp,
    ]);
})->name('session.keep-alive');

Route::controller(SettingsController::class)->middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::get('/', 'data')->name('data');
    Route::post('/', 'update')->name('update');
    Route::post('part-time-divisions', 'partTimeDivisions')->name('part-time-divisions');
    Route::post('ingame-handles', 'ingameHandles')->name('ingame-handles');
    Route::post('transfer-request', [MemberTransferController::class, 'store'])->name('transfer-request');
    Route::post('sync-avatar', 'syncAvatar')->middleware('throttle:3,1')->name('sync-avatar');
});

Route::middleware('auth')->post('feedback', function (Request $request) {
    $request->validate([
        'body'          => 'required|string|max:2000',
        'url'           => 'nullable|string|max:2048',
        'screenshots'   => 'nullable|array|max:3',
        'screenshots.*' => 'nullable|string',
    ]);

    Feedback::create([
        'user_id'     => auth()->id(),
        'body'        => $request->body,
        'url'         => $request->url,
        'screenshots' => $request->screenshots ?: null,
    ]);

    return response()->json(['success' => true]);
})->name('feedback.store');

Route::get('bot/commands/{command}', [BotCommandController::class, 'index'])->name('bot.commands')->middleware('bot');
Route::get('admin/login', fn () => redirect('login'))->name('filament.admin.auth.login');
