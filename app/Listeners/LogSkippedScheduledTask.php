<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Support\Facades\Log;

class LogSkippedScheduledTask
{
    public function handle(ScheduledTaskSkipped $event): void
    {
        Log::info('Scheduled task skipped', [
            'task'   => $event->task->getSummaryForDisplay(),
            'reason' => $event->reason?->value,
        ]);
    }
}
