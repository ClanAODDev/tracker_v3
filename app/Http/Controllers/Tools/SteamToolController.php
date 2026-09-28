<?php

namespace App\Http\Controllers\Tools;

use App\Exceptions\SteamApiException;
use App\Http\Controllers\Controller;
use App\Services\SteamApiService;
use App\Support\Steam\SteamIdParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;

#[Middleware('auth')]
class SteamToolController extends Controller
{
    public function resolveVanityUrl(Request $request, SteamApiService $steam): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'max:255'],
        ]);

        if (! config('services.steam.api_key')) {
            return response()->json(['message' => 'Steam API key is not configured.'], 500);
        }

        $parsed = SteamIdParser::parse($validated['input']);

        if ($parsed->isInvalid()) {
            return response()->json(['message' => "{$parsed->reason}."], 422);
        }

        if (! $parsed->needsLookup()) {
            return response()->json(['steamId' => $parsed->steamId, 'resolved' => false]);
        }

        try {
            $steamId = $steam->resolveVanity($parsed->vanity);
        } catch (SteamApiException) {
            return response()->json(['message' => 'Steam is not responding right now. Try again in a minute.'], 503);
        }

        if (! $steamId) {
            return response()->json(['message' => 'No Steam account found for that custom URL.'], 404);
        }

        return response()->json(['steamId' => $steamId, 'resolved' => true]);
    }
}
