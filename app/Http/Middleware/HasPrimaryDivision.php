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
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!$user->member) {
                abort(408, 'No associated member record');

                return false;
            }

            if (!$user->member->division) {
                //Auth::logout();
                abort(408, 'You do not have a primary division.');

                return false;
            }
        }

        return $next($request);
    }
}
