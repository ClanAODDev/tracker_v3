<?php

namespace App\Traits;

trait RetryableNotification
{
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
        // Log the exception or perform any necessary action
        logger()->error('Notification failed:', ['error' => $exception->getMessage()]);
    }
}
