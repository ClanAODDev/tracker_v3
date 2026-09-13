<?php

use App\Http\Middleware\DivisionMustBeActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HasPrimaryDivision;
use App\Http\Middleware\IsBanned;
use App\Http\Middleware\LogApiRequests;
use App\Http\Middleware\MustBeAdmin;
use App\Http\Middleware\MustBeDeveloper;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\VerifyBotToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Inertia\Inertia;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->validateCsrfTokens(except: [
            'bot/commands',
            'oauth/authorize',
            'oauth/clients',
        ]);

        $middleware->encryptCookies(except: [
            'aod_sessionhash',
        ]);

        $middleware->redirectGuestsTo('/login');

        $middleware->web(append: [
            HasPrimaryDivision::class,
            IsBanned::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->api(append: [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:60,1',
            LogApiRequests::class,
        ])->appendToGroup('api', 'auth:sanctum');

        $middleware->alias([
            'developer'      => MustBeDeveloper::class,
            'admin'          => MustBeAdmin::class,
            'activeDivision' => DivisionMustBeActive::class,
            'banned'         => IsBanned::class,
            'bot'            => VerifyBotToken::class,
            'guest'          => RedirectIfAuthenticated::class,
            'abilities'      => CheckAbilities::class,
            'ability'        => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $exceptions->render(function (InvalidSignatureException $e, Request $request) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([], 403);
            }

            return Inertia::render('errors/show', [
                'status'  => 403,
                'title'   => 'Link expired',
                'message' => 'The link you tried to use is expired or invalid. Contact your division leadership for a new one.',
            ])->toResponse($request)->setStatusCode(403);
        });

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
                return $response;
            }

            if ($status === 408) {
                return Inertia::render('errors/no-primary-division', [
                    'impersonating' => $request->hasSession() && $request->session()->get('impersonating'),
                ])->toResponse($request)->setStatusCode(408);
            }

            if (! app()->hasDebugModeEnabled() && in_array($status, [400, 403, 404, 405, 409, 419, 500, 503], true)) {
                return Inertia::render('errors/show', [
                    'status' => $status,
                    'path'   => in_array($status, [404, 405], true) ? $request->method() . ' /' . ltrim($request->path(), '/') : null,
                ])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })
    ->create();
