<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        $token = $request->user()?->currentAccessToken();
        $tokenName = $token && property_exists($token, 'name') ? $token->name : null;

        $context = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'token_name' => $tokenName,
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ];

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $context['payload'] = $request->except(['password', 'password_confirmation', 'token']);
        }

        if ($response->getStatusCode() >= 400) {
            $context['response'] = substr($response->getContent(), 0, 500);
            Log::channel('api')->error('API Request Failed', $context);
        } else {
            Log::channel('api')->info('API Request', $context);
        }

        return $response;
    }
}
