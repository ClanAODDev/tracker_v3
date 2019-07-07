<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DivisionMustBeActive
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
        if ($request->division->active && $request->division) {
            return $next($request);
        }

        abort(404);
    }
}
