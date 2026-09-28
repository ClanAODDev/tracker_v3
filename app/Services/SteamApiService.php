<?php

namespace App\Services;

use App\Exceptions\SteamApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Throwable;

class SteamApiService
{
    private const BASE_URL = 'https://api.steampowered.com';

    private int $minimumIntervalMs = 0;

    private ?float $lastRequestAt = null;

    public function withMinimumInterval(int $milliseconds): static
    {
        $this->minimumIntervalMs = max(0, $milliseconds);

        return $this;
    }

    public function resolveVanity(string $vanity): ?string
    {
        $cacheKey = 'steam:vanity:' . strtolower($vanity);
        $cached   = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached ?: null;
        }

        $result  = $this->get('/ISteamUser/ResolveVanityURL/v1/', ['vanityurl' => $vanity]);
        $steamId = ($result['success'] ?? null) === 1 ? (string) $result['steamid'] : null;

        Cache::put($cacheKey, $steamId ?? '', $steamId ? now()->addDays(30) : now()->addDay());

        return $steamId;
    }

    /**
     * @param  array<int, string>  $steamIds
     * @return array<string, array{personaName: string, profileUrl: string}>
     */
    public function playerSummaries(array $steamIds): array
    {
        $summaries = [];

        foreach (array_chunk(array_values(array_unique($steamIds)), 100) as $chunk) {
            $players = $this->get('/ISteamUser/GetPlayerSummaries/v2/', ['steamids' => implode(',', $chunk)])['players'] ?? [];

            foreach ($players as $player) {
                $summaries[(string) $player['steamid']] = [
                    'personaName' => (string) ($player['personaname'] ?? ''),
                    'profileUrl'  => (string) ($player['profileurl'] ?? ''),
                ];
            }
        }

        return $summaries;
    }

    private function get(string $path, array $query): array
    {
        $apiKey = config('services.steam.api_key');

        if (! $apiKey) {
            throw SteamApiException::missingKey();
        }

        $this->waitForSlot();

        try {
            $response = Http::timeout(10)
                ->retry(3, fn (int $attempt) => $attempt * 2000, fn (Throwable $e) => $this->isRetryable($e), throw: false)
                ->get(self::BASE_URL . $path, [...$query, 'key' => $apiKey]);
        } catch (ConnectionException $e) {
            throw SteamApiException::unreachable($e);
        } finally {
            $this->lastRequestAt = microtime(true);
        }

        if ($response->failed()) {
            throw SteamApiException::fromResponse($response);
        }

        return $response->json('response', []);
    }

    private function isRetryable(Throwable $e): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }

        return $e instanceof RequestException
            && ($e->response->status() === 429 || $e->response->serverError());
    }

    private function waitForSlot(): void
    {
        if ($this->lastRequestAt === null || $this->minimumIntervalMs === 0) {
            return;
        }

        $elapsedMs = (microtime(true) - $this->lastRequestAt) * 1000;

        if ($elapsedMs < $this->minimumIntervalMs) {
            Sleep::usleep((int) (($this->minimumIntervalMs - $elapsedMs) * 1000));
        }
    }
}
