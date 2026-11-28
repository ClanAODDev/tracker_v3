<?php

namespace App\Http\Middleware;

use App\Enums\Ability;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanViewLogs
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->can(Ability::ViewLogs), 403, 'You are not authorized to access this area.');

        return $next($request);
    }
}
