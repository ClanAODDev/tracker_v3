<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AODBotService
{
    private $baseUrl;

    private $token;

    public function __construct()
    {
        $this->baseUrl = config('aod.bot_api_base_url');
        $this->token   = config('aod.discord_bot_token');
    }

    private function buildHeaders(): array
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->token),
        ];

        if (auth()->check()) {
            $headers['X-Requested-By'] = auth()->user()->member?->discord_id;
        }

        return $headers;
    }

    private function send(string $url, string $method = 'GET', ?array $body = null): Response
    {
        $request = Http::withHeaders($this->buildHeaders())
            ->withOptions(['verify' => config('aod.bot_api_verify_tls', false)])
            ->timeout(config('aod.external_request.timeout'))
            ->connectTimeout(config('aod.external_request.connect_timeout'))
            ->retry(config('aod.external_request.retry_times'), config('aod.external_request.retry_delay_ms'))
            ->throw();

        return $body !== null
            ? $request->send($method, $url, ['json' => $body])
            : $request->send($method, $url);
    }

    /**
     * Get AOD forum member info
     */
    public function getForumMember($member_id): Response
    {
        $url = sprintf('%s/forum_member/%s', $this->baseUrl, $member_id);

        return $this->send($url);
    }

    /**
     * Requests an ad-hoc member sync
     */
    public function updateDiscordMember($discord_member_id): Response
    {
        $url = sprintf('%s/members/%s/update', $this->baseUrl, $discord_member_id);

        return $this->send($url);
    }

    public function getMemberAvatar(string|int $discord_id): ?string
    {
        $url      = sprintf('%s/members/%s', $this->baseUrl, $discord_id);
        $response = $this->send($url);

        return $response->json('avatarHash');
    }
}
