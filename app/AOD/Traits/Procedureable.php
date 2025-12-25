<?php

namespace App\AOD\Traits;

use DB;
use Exception;
use Log;

trait Procedureable
{
    /**
     * should not be called directly, move implementation to a service class
     */
    private function callProcedure($procedure, $data, $connection = 'aod_forums')
    {
        /*
        $backtrace = debug_backtrace();
        $caller = $backtrace[1] ?? null;

        if ($caller && isset($caller['class']) && !str_contains($caller['class'], 'Procedureable')) {
            throw new \RuntimeException("Direct access to callProcedure is not allowed.");
        }
        */

        try {
            if (\is_array($data)) {
                $stringKeys = implode(',', array_map(fn ($key) => ':' . $key, array_keys($data)));
                $results = collect(DB::connection($connection)
                    ->select("CALL {$procedure}({$stringKeys})", $data))->first();
            } elseif (\is_string($data) || \is_int($data)) {
                $results = collect(DB::connection($connection)
                    ->select("CALL {$procedure}('{$data}')"))->first();
            }

            if (! isset($results) || ! property_exists($results, 'userid')) {
                return collect([]);
            }

            return $results;
        } catch (Exception $exception) {
            Log::error("Could not call procedure: {$procedure}", [
                'exception' => $exception->getMessage(),
            ]);

            return collect([]);
        }
    }

    public function checkValidSessionData($sessionData): bool
    {
        $this->callProcedure('check_session', [
            'session' => $_COOKIE[$this->sessionKey],
        ]);

        return isset($sessionData->userid, $sessionData->username, $sessionData->email, $sessionData->membergroupids, $sessionData->usergroupid);
    }
}
