<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForumProcedureService
{
    protected string $connection = 'aod_forums';

    private const CHECK_SESSION = 'check_session';

    private const CHECK_USER = 'check_user';

    private const GET_USER = 'get_user';

    private const SET_USER_RANK = 'set_user_rank';

    private const SET_USER_DIVISION = 'set_user_division';

    public function checkSession(string $sessionHash): ?object
    {
        return $this->call(self::CHECK_SESSION, ['session' => $sessionHash]);
    }

    public function getUser(int $userId): ?object
    {
        return $this->call(self::GET_USER, ['userid' => $userId]);
    }

    public function setUserRank(int $userId, string $rank): ?object
    {
        return $this->call(self::SET_USER_RANK, [
            'userid' => $userId,
            'rank' => $rank,
        ]);
    }

    public function setUserDivision(int $userId, string $division): ?object
    {
        return $this->call(self::SET_USER_DIVISION, [
            'userid' => $userId,
            'division' => $division,
        ]);
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
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
