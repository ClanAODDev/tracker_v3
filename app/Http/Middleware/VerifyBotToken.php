<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyBotToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $app_tokens = explode(',', config('aod.bot_cmd_tokens'));

        if (\in_array($request->token, $app_tokens, true) && ! empty($request->token)) {
            return $next($request);
        }

        return response()->json([
            'text' => 'Either an invalid token was provided, or the request didn\'t originate from a bot command.',
        ], 401);
    }
}
