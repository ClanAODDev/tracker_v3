<?php

namespace App\Services;

use App\Enums\ForumGroup;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;
use RuntimeException;

class AODForumService
{
    private const AGENT = 'AOD Division Tracker';

    private const DEFAULT_TOKEN_PARAM = 'authcode2';

    private const INFO_URL = 'https://www.clanaod.net/forums/aodinfo.php';

    private const MODCP_URL = 'https://www.clanaod.net/forums/modcp/aodmember.php';

    private const SUCCESS = 'saved_user_x_successfully';

    const REMOVE_FROM_AOD = 'remaod';

    const ADD_TO_AOD = 'addaod';

    const CREATE_USER = 'newuser';

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
            $last  = end($parts);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return 'Error: Invalid user context';
        }

        $json = $resp->json();

        return $json !== null ? $json : $last;
    }

    /**
     * Adds a forum account to AOD (ForumGroup::MEMBER)
     */
    public static function addForumMember(
        int $impersonatingMemberId,
        int $memberIdBeingAdded,
        string $rank,
        string $name,
        string $division
    ): array|string {
        $response = self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid'   => $impersonatingMemberId,
            'do'           => self::ADD_TO_AOD,
            'aodname'      => $name,
            'rank'         => $rank,
            'division'     => $division,
            'u'            => $memberIdBeingAdded,
        ]);

        if (! is_string($response) || ! str_contains($response, self::SUCCESS)) {
            throw new RuntimeException("Failed to add member to AOD - $name - $response");
        }

        return true;
    }

    /**
     * Removes a forum account from AOD
     */
    public static function removeForumMember(
        int $memberIdBeingRemoved,
        int $impersonatingMemberId,
    ): array|string {
        $response = self::request(self::MODCP_URL, [
            '_token_param' => 'authcode',
            'aod_userid'   => $impersonatingMemberId,
            'do'           => self::REMOVE_FROM_AOD,
            'u'            => $memberIdBeingRemoved,
        ]);

        if (! is_string($response) || ! str_contains($response, self::SUCCESS)) {
            throw new RuntimeException("Failed to remove member $memberIdBeingRemoved - $response");
        }

        return true;
    }

    /**
     * Creates a new forum account
     */
    public static function createForumAccount(
        int $impersonatingMemberId,
        string $username,
        string $email,
        string $dateOfBirth,
        string $password,
        string $discordId,
        ForumGroup $forumGroup = ForumGroup::AWAITING_MODERATION,
    ): array {
        $response = self::postRequest(self::MODCP_URL, [
            'aod_userid'  => $impersonatingMemberId,
            'do'          => self::CREATE_USER,
            'username'    => $username,
            'email'       => $email,
            'dob'         => $dateOfBirth,
            'password'    => $password,
            'discord_id'  => $discordId,
            'usergroupid' => $forumGroup->value,
        ]);

        if (is_string($response) && str_contains($response, 'saved_user') && str_contains($response, 'successfully')) {
            return ['success' => true];
        }

        $error = is_string($response) ? $response : 'Unknown error';

        if (str_contains($error, 'invalid_user_specified')) {
            $error = 'Invalid user. Email may already be registered on the forums.';
        }

        return [
            'success' => false,
            'error'   => $error,
        ];
    }

    public static function postRequest(string $url, array $options = []): array|string
    {
        $params = array_merge($options, [
            'authcode' => self::generateToken(),
        ]);

        try {
            $resp = Http::withUserAgent(self::AGENT)
                ->timeout(10)
                ->connectTimeout(5)
                ->retry(2, 200)
                ->asForm()
                ->post($url, $params);

            $json = $resp->json();

            if ($json !== null) {
                return $json;
            }

            $clean = strip_tags($resp->body());
            $clean = preg_replace('/\s+/', ' ', $clean);

            return trim($clean);
        } catch (Exception $e) {
            Log::error('Forum POST request failed: ' . $e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    public static function fetchInfo(array $params = []): array|string
    {
        return self::request(self::INFO_URL, $params);
    }

    public function authenticate(string $username, string $password): ?array
    {
        try {
            $results = DB::connection('aod_forums')
                ->select(
                    'CALL check_user(:username, :password)',
                    [
                        'username' => $username,
                        'password' => md5($password),
                    ]
                );
        } catch (Exception $exception) {
            Log::error('AOD Authentication failed: ' . $exception->getMessage());

            return null;
        }

        if (empty($results)) {
            return null;
        }

        $member = Arr::first($results);

        if (! ($member->valid ?? false)) {
            return null;
        }

        return [
            'clan_id' => (int) $member->userid,
            'email'   => $member->email,
            'roles'   => array_merge(
                array_map('intval', explode(',', $member->membergroupids)),
                [(int) $member->usergroupid]
            ),
        ];
    }

    public function getUserByEmail(string $email): ?object
    {
        try {
            $results = DB::connection('aod_forums')
                ->select('CALL get_user_by_email(:email)', ['email' => $email]);
        } catch (Exception $exception) {
            Log::error('getUserByEmail failed: ' . $exception->getMessage());

            return null;
        }

        return Arr::first($results);
    }

    private static function generateToken(): string
    {
        $currentMinute = (int) floor(time() / 60) * 60;

        return md5($currentMinute . config('aod.token'));
    }
}
