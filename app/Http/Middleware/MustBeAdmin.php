<?php

namespace App\Http\Middleware;

use Closure;

class MustBeAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->isRole('admin') or
            $request->user()->isDeveloper()
        ) {
            return $next($request);
        }

        abort(403, 'You are not authorized to access this area.');
    }
}
