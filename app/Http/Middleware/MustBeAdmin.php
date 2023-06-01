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
    public function handle($request, Closure $next)
    {
        if (\Auth::check() && $request->user()->isRole('administrator')) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
