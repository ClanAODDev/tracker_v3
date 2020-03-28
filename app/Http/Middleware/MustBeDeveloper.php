<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MustBeDeveloper
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check() && $request->user()->developer) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
