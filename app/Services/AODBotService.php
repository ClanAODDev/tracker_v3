<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class AODBotService
{
    private $baseUrl;

    private $token;

    private $client;

    public function __construct()
    {
        $this->baseUrl = config('app.aod.bot_api_base_url');
        $this->token = config('app.aod.discord_bot_token');
        $this->client = new Client;
    }

    private function buildHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->token),
        ];

        if (auth()->check()) {
            array_merge($headers, ['X-Requested-By' => auth()->user()->member->discord_id]);
        }

        return $headers;
    }

    private function send($url, $method = 'GET', $body = null): ResponseInterface
    {
        return $this->client->send(new Request($method, $url, $this->buildHeaders(), $body), ['verify' => false]);
    }

    /**
     * Get AOD forum member info
     *
     * @throws GuzzleException
     */
    public function getForumMember($member_id): ResponseInterface
    {
        $url = sprintf('%s/forum_member/%s', $this->baseUrl, $member_id);

        return $this->send($url);
    }

    /**
     * Requests an ad-hoc member sync
     *
     * @throws GuzzleException
     */
    public function updateDiscordMember($discord_member_id): ResponseInterface
    {
        $url = sprintf('%s/members/%s/update', $this->baseUrl, $discord_member_id);

        return $this->send($url);
    }
}
