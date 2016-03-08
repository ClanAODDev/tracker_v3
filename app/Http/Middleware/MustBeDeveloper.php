<?php

namespace App\Http\Middleware;

use Closure;

class MustBeDeveloper
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
        $user = $request->user();

        if ($user->developer) {
            return $next($request);
        }

        abort(333, 'You are not authorized to access this area.');
    }
}
