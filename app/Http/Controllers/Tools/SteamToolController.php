<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Http;

#[Middleware('auth')]
class SteamToolController extends Controller
{
    public function resolveVanityUrl(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'max:255'],
        ]);

        $apiKey = config('services.steam.api_key');

        if (! $apiKey) {
            return response()->json(['message' => 'Steam API key is not configured.'], 500);
        }

        $vanity = $this->extractVanity($validated['input']);

        if (ctype_digit($vanity) && strlen($vanity) >= 15) {
            return response()->json(['steamId' => $vanity, 'resolved' => false]);
        }

        $response = Http::get('https://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/', [
            'key'       => $apiKey,
            'vanityurl' => $vanity,
        ]);

        $result = $response->json('response', []);

        if (($result['success'] ?? null) !== 1) {
            return response()->json([
                'message' => $result['message'] ?? 'No Steam account found for that vanity URL.',
            ], 404);
        }

        return response()->json(['steamId' => $result['steamid'], 'resolved' => true]);
    }

    /**
     * Accepts a bare vanity name, a full /id/ or /profiles/ profile URL, or an
     * already-resolved SteamID64, and pulls out just the part worth resolving.
     */
    private function extractVanity(string $input): string
    {
        $input = trim($input);

        if (preg_match('~steamcommunity\.com/id/([^/?#]+)~i', $input, $matches)) {
            return $matches[1];
        }

        if (preg_match('~steamcommunity\.com/profiles/(\d+)~i', $input, $matches)) {
            return $matches[1];
        }

        return $input;
    }
}
