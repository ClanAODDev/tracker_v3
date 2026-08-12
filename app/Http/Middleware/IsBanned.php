<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;

class IsBanned
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (auth()->check() && $request->user()->isRole(Role::BANNED)) {
            abort(403, 'You are banned.');
        }

        return $next($request);
    }
}
