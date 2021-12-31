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

            if (!$user->member->division || !$user->member) {
                if (session('impersonating')) {
                    auth()->logout();
                    redirect()->to(route('end-impersonation'));
                }

                //Auth::logout();
                abort(408, 'You do not have a primary division.');

                return false;
            }
        }

        return $next($request);
    }
}
