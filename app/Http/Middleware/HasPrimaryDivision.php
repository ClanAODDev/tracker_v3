<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasPrimaryDivision
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isPendingRegistration()) {
                if (! $request->routeIs('auth.discord.*', 'logout')) {
                    return redirect()->route('auth.discord.pending');
                }

                return $next($request);
            }

            if (! $user->member || ! $user->member->division) {
                if ($request->routeIs('logout')) {
                    return $next($request);
                }

                if (session('impersonating')) {
                    auth()->logout();
                    redirect()->to(route('end-impersonation'));
                }

                abort(408, 'You do not have a primary division.');

                return false;
            }
        }

        return $next($request);
    }
}
