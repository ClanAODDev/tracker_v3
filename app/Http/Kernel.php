<?php

namespace App\Http;

use App\Http\Middleware\CheckForMaintenanceMode;
use App\Http\Middleware\ClanForumAuthentication;
use App\Http\Middleware\DivisionMustBeActive;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\HasPrimaryDivision;
use App\Http\Middleware\IsBanned;
use App\Http\Middleware\MustBeAdmin;
use App\Http\Middleware\MustBeDeveloper;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\VerifySlackToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Passport\Http\Middleware\CheckForAnyScope;
use Laravel\Passport\Http\Middleware\CheckScopes;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            HasPrimaryDivision::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
            VerifyCsrfToken::class,
            IsBanned::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
            'auth:api'
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'forumAuth' => ClanForumAuthentication::class,
        'auth' => Authenticate::class,
        'bindings' => SubstituteBindings::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,

        'developer' => MustBeDeveloper::class,
        'admin' => MustBeAdmin::class,
        'activeDivision' => DivisionMustBeActive::class,
        'banned' => IsBanned::class,
        'slack' => VerifySlackToken::class,
        'scopes' => CheckScopes::class,
        'scope' => CheckForAnyScope::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\ClanForumAuthentication::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,


        \App\Http\Middleware\MustBeDeveloper::class,
        \App\Http\Middleware\MustBeAdmin::class,
        \App\Http\Middleware\DivisionMustBeActive::class,
        \App\Http\Middleware\IsBanned::class,
        \App\Http\Middleware\VerifySlackToken::class,
        CheckScopes::class,
        CheckForAnyScope::class,
    ];
}
