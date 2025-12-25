<?php

namespace App\Traits;

use Log;

trait RetryableJob
{
    public $tries = 4;

    public function backoff()
    {
        return [
            30,
            120,
            1800,
            3600,
        ];
    }

    public function handleFailure($exception)
    {
        Log::error('Job failed:', ['error' => $exception->getMessage()]);
    }
}
