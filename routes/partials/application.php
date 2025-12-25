<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\Bot\BotCommandController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\TrainingController;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [AppController::class, 'index'])->name('index');
Route::get('home', [AppController::class, 'index'])->name('home');

Route::controller(ImpersonationController::class)->group(function () {
    Route::get('impersonate/{user}', 'impersonate')->name('impersonate');
    Route::get('impersonate-end', 'endImpersonation')->name('end-impersonation');
});

Route::controller(TrainingController::class)->prefix('training')->name('training.')->group(function () {
    Route::get('sgt', 'sgtTraining')->name('sgt');
    Route::get('ssgt', 'index')->name('ssgt');
    Route::get('msgt', 'index')->name('msgt');
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

Route::middleware('auth')->prefix('settings')->name('settings.')->group(function () {
    Route::post('/', function (Request $request) {
        $user = auth()->user();
        $user->settings = array_merge($user->settings, [
            'disable_animations' => filter_var($request->input('disable_animations'), FILTER_VALIDATE_BOOLEAN),
            'mobile_nav_side' => $request->input('mobile_nav_side', 'right'),
            'snow' => $request->input('snow', 'no_snow'),
            'ticket_notifications' => filter_var($request->input('ticket_notifications'), FILTER_VALIDATE_BOOLEAN),
        ]);
        $user->save();

        return response()->json(['success' => true]);
    })->name('update');

    Route::post('part-time-divisions', function (Request $request) {
        $member = auth()->user()->member;
        if (! $member) {
            return response()->json(['error' => 'No member record'], 400);
        }

        $divisionIds = $request->input('divisions', []);
        $activeIds = Division::active()->pluck('id')->all();
        $validIds = array_values(array_intersect($divisionIds, $activeIds));
        $member->partTimeDivisions()->sync($validIds);

        return response()->json(['success' => true, 'count' => count($validIds)]);
    })->name('part-time-divisions');

    Route::post('ingame-handles', function (Request $request) {
        $member = auth()->user()->member;
        if (! $member) {
            return response()->json(['error' => 'No member record'], 400);
        }

        $handles = $request->input('handles', []);
        \App\Filament\Forms\Components\IngameHandlesForm::saveHandles($member, $handles);

        return response()->json(['success' => true, 'count' => $member->memberHandles()->count()]);
    })->name('ingame-handles');
});

Route::get('bot/commands/{command}', [BotCommandController::class, 'index'])->name('bot.commands')->middleware('bot');
Route::get('admin/login', fn () => redirect('login'))->name('filament.admin.auth.login');
