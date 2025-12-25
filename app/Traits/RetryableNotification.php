<?php

namespace App\Traits;

use Log;

trait RetryableNotification
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
        Log::error('Notification failed:', ['error' => $exception->getMessage()]);
    }
}
