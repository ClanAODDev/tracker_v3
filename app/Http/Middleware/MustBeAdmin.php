<?php

namespace App\Http\Middleware;

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

        if (auth()->check() && $request->user()->isRole('admin')) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
