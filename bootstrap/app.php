<?php

use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\DivisionMustBeActive;
use App\Http\Middleware\HasPrimaryDivision;
use App\Http\Middleware\IsBanned;
use App\Http\Middleware\MustBeAdmin;
use App\Http\Middleware\MustBeDeveloper;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyBotToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        __DIR__ . '/../routes/console.php',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            CheckForMaintenanceMode::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            TrustProxies::class,
        ]);

        $middleware->web(append: [
            HasPrimaryDivision::class,
            IsBanned::class,
        ]);

        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
        ])->appendToGroup('api', 'auth:sanctum');

        $middleware->alias([
            'developer' => MustBeDeveloper::class,
            'admin' => MustBeAdmin::class,
            'activeDivision' => DivisionMustBeActive::class,
            'banned' => IsBanned::class,
            'bot' => VerifyBotToken::class,
            'guest' => RedirectIfAuthenticated::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $exceptions->render(function (InvalidSignatureException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([], 403);
            }

            return response()->view('errors.link-expired', [], 403);
        });
    })
    ->create();
