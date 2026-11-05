<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForumProcedureService
{
    protected string $connection = 'aod_forums';

    private const CHECK_USER = 'check_user';

    private const GET_USER = 'get_user';

    private const SET_USER_RANK = 'set_user_rank';

    private const SET_USER_DIVISION = 'set_user_division';

    private const SET_DISCORD_INFO = 'set_discord_info';

    public const MAX_USER_ID = 2147483647;

    public static function isValidUserId(int $userId): bool
    {
        return $userId >= 1 && $userId <= self::MAX_USER_ID;
    }

    public function getUser(int $userId): ?object
    {
        if (! self::isValidUserId($userId)) {
            return null;
        }

        return $this->call(self::GET_USER, ['userid' => $userId]);
    }

    public function setUserRank(int $userId, string $rank): ?object
    {
        if (! self::isValidUserId($userId)) {
            return null;
        }

        return $this->call(self::SET_USER_RANK, [
            'userid' => $userId,
            'rank'   => $rank,
        ]);
    }

    public function setUserDivision(int $userId, string $division): ?object
    {
        if (! self::isValidUserId($userId)) {
            return null;
        }

        return $this->call(self::SET_USER_DIVISION, [
            'userid'   => $userId,
            'division' => $division,
        ]);
    }

    public function setDiscordInfo(int $userId, string $discordId, string $discordTag): ?object
    {
        if (! self::isValidUserId($userId)) {
            return null;
        }

        return $this->call(self::SET_DISCORD_INFO, [
            'user_id'     => $userId,
            'discord_id'  => $discordId,
            'discord_tag' => $discordTag,
        ]);
    }

    public function clearDiscordInfo(int $userId): ?object
    {
        return $this->setDiscordInfo($userId, '', '');
    }

    public function checkUser(string $username, string $password): ?object
    {
        return $this->call(self::CHECK_USER, [
            'username' => $username,
            'password' => md5($password),
        ]);
    }

    protected function call(string $procedure, array $params): ?object
    {
        try {
            $placeholders = implode(',', array_map(fn ($key) => ':' . $key, array_keys($params)));

            $results = DB::connection($this->connection)
                ->select("CALL {$procedure}({$placeholders})", $params);

            return $results[0] ?? null;
        } catch (\Exception $exception) {
            Log::error("Forum procedure failed: {$procedure}", [
                'params' => $params,
                'error'  => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
