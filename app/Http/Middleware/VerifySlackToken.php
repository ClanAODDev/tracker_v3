<?php

namespace app\Http\Middleware;

use Closure;

class VerifySlackToken
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app_token = env('SLACK_TOKEN');

        if (isset($request->token) && $request->token === $app_token) {
            return $next($request);
        }

        return response()->json([
            'text' => 'Invalid token',
        ]);

    }
}
