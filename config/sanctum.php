<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    | This app's own domain is deliberately NOT included by default: no
    | frontend here consumes /api/v1 over a session cookie, and a
    | TransientToken (attached to stateful requests) satisfies every
    | ability check unconditionally, which would silently bypass the
    | abilities:division:read/division:write gates on those routes for
    | any logged-in user. Only add this app's own domain here if a
    | first-party SPA is intentionally built against /api/v1, and pair it
    | with real per-route authorization that doesn't rely solely on
    | tokenCan().
    |
    */

    'stateful' => explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1'
    )),

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => null,

    /*
   |--------------------------------------------------------------------------
   | Token Prefix
   |--------------------------------------------------------------------------
   |
   | Sanctum can prefix new tokens in order to take advantage of numerous
   | security scanning initiatives maintained by open source platforms
   | that notify developers if they commit tokens into repositories.
   |
   | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
   |
   */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies'      => EncryptCookies::class,
        'validate_csrf_token'  => ValidateCsrfToken::class,
    ],
];
