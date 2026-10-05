<?php

namespace App\Http\Middleware;

use App\Enums\Ability;
use Closure;
use Illuminate\Http\Request;

class MustBeAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (auth($guard)->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('login');
        }

        if (auth()->check() && $request->user()->can(Ability::ViewAdminPages)) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
