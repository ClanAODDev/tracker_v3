<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsBanned
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (Auth::check() && $request->user()->isRole(Role::BANNED)) {
            abort(403, 'You are banned.');
        }

        return $next($request);
    }
}
