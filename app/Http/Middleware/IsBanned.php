<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;

class IsBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check() && Role::BANNED === $request->user()->role) {
            abort(403, 'You are banned.');
        }

        return $next($request);
    }
}
