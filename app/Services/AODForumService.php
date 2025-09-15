<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AODForumService
{
    private const AGENT = 'AOD Division Tracker';

    private const DEFAULT_TOKEN_PARAM = 'authcode2';

    private const INFO_URL = 'https://www.clanaod.net/forums/aodinfo.php';

    private const MODCP_URL = 'https://www.clanaod.net/forums/modcp/aodmember.php';

    private const SUCCESS = 'saved_user_x_successfully';

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

            $clean = strip_tags($resp->body());
            $clean = preg_replace('/\s+/', ' ', $clean);
            $parts = explode(' ', trim($clean));
            $last = end($parts);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return 'Error: Invalid user context';
        }

        $json = $resp->json();

        return $json !== null ? $json : $last;
    }

    public static function addForumMember(
        int $impersonatingMemberId,
        int $memberIdBeingAdded,
        string $rank,
        string $name,
        string $division
    ): array|string {
        $response = self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid' => $impersonatingMemberId,
            'do' => 'addaod',
            'aodname' => $name,
            'rank' => $rank,
            'division' => $division,
            'u' => $memberIdBeingAdded,
        ]);

        if (! is_string($response) || ! str_contains($response, self::SUCCESS)) {
            throw new \RuntimeException("Failed to add member $name - $response");
        }

        return true;
    }

    public static function removeForumMember(
        int $memberIdBeingRemoved,
        int $impersonatingMemberId,
    ): array|string {
        $response = self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid' => $impersonatingMemberId,
            'do' => 'remaod',
            'u' => $memberIdBeingRemoved,
        ]);

        if (! is_string($response) || ! str_contains($response, self::SUCCESS)) {
            throw new \RuntimeException("Failed to remove member $memberIdBeingRemoved - $response");
        }

        return true;
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
