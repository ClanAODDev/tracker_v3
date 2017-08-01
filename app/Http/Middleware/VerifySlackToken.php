<?php

namespace App\Http\Middleware;

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
        $app_tokens = explode(',', config('SLACK_TOKENS'));

        if (in_array($request->token, $app_tokens) && ! empty($request->token)) {
            return $next($request);
        }

        return response()->json([
            'text' => 'Either an invalid token was provided, or the request didn\'t originate from Slack',
        ]);
    }
}
