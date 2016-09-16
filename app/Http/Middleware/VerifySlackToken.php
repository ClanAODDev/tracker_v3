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
        $app_tokens = explode(
            ',',
            env('SLACK_TOKENS')
        );

        if (in_array($request->token, $app_tokens)) {
            return $next($request);
        }

        return response()->json([
            'text' => 'Either an invalid token was provided, or the request didn\'t originate from Slack',
        ]);
    }
}
