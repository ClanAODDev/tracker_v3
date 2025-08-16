<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AODForumService
{
    private const AGENT = 'AOD Division Tracker';
    private const DEFAULT_TOKEN_PARAM = 'authcode2';

    private const INFO_URL   = 'https://www.clanaod.net/forums/aodinfo.php';
    private const MODCP_URL  = 'https://www.clanaod.net/forums/modcp/aodmember.php';

    public static function request(string $url, array $options = []): array|string
    {
        $tokenParam = $options['_token_param'] ?? self::DEFAULT_TOKEN_PARAM;
        unset($options['_token_param']);

        $params = array_merge($options, [
            $tokenParam => self::generateToken(),
        ]);

        try {
            $resp = Http::withUserAgent(self::AGENT)
                ->timeout(10)
                ->connectTimeout(5)
                ->retry(2, 200)
                ->get($url, $params);
        } catch (ConnectionException $e) {
            throw new \RuntimeException("AOD request failed to connect: {$e->getMessage()}", 0, $e);
        }

        if (! $resp->successful()) {
            $body = mb_substr($resp->body() ?? '', 0, 500);
            throw new \RuntimeException("AOD request error {$resp->status()}: {$body}");
        }

        $json = $resp->json();
        return $json !== null ? $json : $resp->body();
    }

    public static function addForumMember(int $memberIdBeingAdded, int $impersonatingId): array|string
    {
        return self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid'   => $impersonatingId,
            'do'           => 'addaod',
            'u'            => $memberIdBeingAdded,
        ]);
    }

    public static function removeForumMember(int $memberIdBeingAdded, int $impersonatingId): array|string
    {
        return self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid'   => $impersonatingId,
            'do'           => 'remaod',
            'u'            => $memberIdBeingAdded,
        ]);
    }

    public static function fetchInfo(array $params = []): array|string
    {
        return self::request(self::INFO_URL, $params);
    }

    private static function generateToken(): string
    {
        $currentMinute = (int) floor(time() / 60) * 60;
        return md5($currentMinute . config('app.aod.token'));
    }
}
