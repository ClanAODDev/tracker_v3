<?php

namespace App\AOD\Traits;

trait Procedureable
{
    private function callProcedure($procedure, $data)
    {
        try {
            if (\is_array($data)) {
                $stringKeys = implode(',', array_map(fn ($key) => ':' . $key, array_keys($data)));
                $results = collect(\DB::connection('aod_forums')
                    ->select("CALL {$procedure}({$stringKeys})", $data))->first();
            } elseif (\is_string($data) || \is_int($data)) {
                $results = collect(\DB::connection('aod_forums')
                    ->select("CALL {$procedure}('{$data}')"))->first();
            }

            if (! isset($results) || ! property_exists($results, 'userid')) {
                return collect([]);
            }

            return $results;
        } catch (\Exception $exception) {
            \Log::error("Could not call procedure: {$procedure}", [
                'exception' => $exception->getMessage(),
            ]);

            return collect([]);
        }
    }
}
