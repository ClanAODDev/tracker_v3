<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class CheckApplicationHealth
{
    public const int STALLED_JOB_MINUTES = 10;

    public const int SCHEDULER_SILENCE_MINUTES = 70;

    public function handle(DiagnosingHealth $event): void
    {
        $this->checkDatabase();
        $this->checkQueue();
        $this->checkScheduler();
    }

    private function checkDatabase(): void
    {
        try {
            DB::select('select 1');
        } catch (\Throwable $exception) {
            throw new RuntimeException('Database unavailable', previous: $exception);
        }
    }

    private function checkQueue(): void
    {
        if (config('queue.default') !== 'database') {
            return;
        }

        $stalled = DB::table(config('queue.connections.database.table', 'jobs'))
            ->whereNull('reserved_at')
            ->where('available_at', '<=', now()->subMinutes(self::STALLED_JOB_MINUTES)->getTimestamp())
            ->exists();

        if ($stalled) {
            throw new RuntimeException('Queue is not being processed');
        }
    }

    private function checkScheduler(): void
    {
        $lastActivity = collect([
            MonitoredScheduledTask::query()->max('last_started_at'),
            MonitoredScheduledTask::query()->max('last_skipped_at'),
        ])->filter()->max();

        if ($lastActivity === null) {
            return;
        }

        if (now()->subMinutes(self::SCHEDULER_SILENCE_MINUTES)->greaterThan($lastActivity)) {
            throw new RuntimeException('Scheduler has not run recently');
        }
    }
}
