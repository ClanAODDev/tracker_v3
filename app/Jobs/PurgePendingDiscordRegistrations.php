<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PurgePendingDiscordRegistrations implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $daysOld = 60
    ) {}

    public function handle(): void
    {
        $count = User::pendingDiscord()
            ->where('created_at', '<', now()->subDays($this->daysOld))
            ->delete();

        Log::info('Purged old pending Discord registrations', [
            'count' => $count,
            'days_old' => $this->daysOld,
        ]);
    }
}
