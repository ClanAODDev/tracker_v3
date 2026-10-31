<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if ($this->shouldLoginLocalUser($request)) {
            auth()->login($this->localUser());

            return redirect()->intended();
        }

        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                return redirect('/home');
            }
        }

        return $next($request);
    }

    private function shouldLoginLocalUser(Request $request): bool
    {
        return str_contains(app()->environment(), 'local')
            && ! auth()->check()
            && ! $request->cookie('tracker_logged_out')
            && User::exists();
    }

    private function localUser(): User
    {
        return config('dev_default_user')
            ? User::findOrFail(config('dev_default_user'))
            : User::first();
    }
}
