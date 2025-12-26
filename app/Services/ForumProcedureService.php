<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForumProcedureService
{
    protected string $connection = 'aod_forums';

    public function checkSession(string $sessionHash): ?object
    {
        return $this->call('check_session', ['session' => $sessionHash]);
    }

    public function getUser(int $userId): ?object
    {
        return $this->call('get_user', ['userid' => $userId]);
    }

    public function setUserRank(int $userId, string $rank): ?object
    {
        return $this->call('set_user_rank', [
            'userid' => $userId,
            'rank' => $rank,
        ]);
    }

    public function setUserDivision(int $userId, string $division): ?object
    {
        return $this->call('set_user_division', [
            'userid' => $userId,
            'division' => $division,
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
