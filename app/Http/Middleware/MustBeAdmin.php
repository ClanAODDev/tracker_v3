<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('login');
        }

        if (\Auth::check() && $request->user()->isRole('admin')) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
