<?php

namespace App\AOD\Traits;

trait Procedureable
{
    /**
     * @param $procedure
     * @param $data
     * @return \Illuminate\Support\Collection
     */
    private function callProcedure($procedure, $data)
    {
        try {
            if (is_array($data)) {
                $stringKeys = implode(',', array_map(function ($key) {
                    return ':' . $key;
                }, array_keys($data)));
                $results = collect(\DB::connection('aod_forums')
                    ->select("CALL {$procedure}({$stringKeys})", $data))->first();
            } elseif (is_string($data) || is_int($data)) {
                $results = collect(\DB::connection('aod_forums')
                    ->select("CALL {$procedure}('{$data}')"))->first();
            }

            if (!isset($results) || !property_exists($results, 'userid')) {
                return collect([]);
            }

            return $results;
        } catch (\Exception $exception) {
            \Log::error("Could not call procedure: {$procedure}", [
                'exception' => $exception->getMessage()
            ]);
        }
    }
}
