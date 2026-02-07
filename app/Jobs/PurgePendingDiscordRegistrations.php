<?php

namespace App\Jobs;

use App\Models\DivisionApplication;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Parallax\FilamentComments\Models\FilamentComment;

class PurgePendingDiscordRegistrations implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $daysOld = 30
    ) {}

    public function handle(): void
    {
        $cutoff = now()->subDays($this->daysOld);

        $applicationIds = DivisionApplication::pending()
            ->where('created_at', '<', $cutoff)
            ->pluck('id');

        FilamentComment::where('subject_type', (new DivisionApplication)->getMorphClass())
            ->whereIn('subject_id', $applicationIds)
            ->forceDelete();

        $applicationCount = $applicationIds->count();
        DivisionApplication::whereIn('id', $applicationIds)->delete();

        $userCount = User::pendingDiscord()
            ->where('created_at', '<', $cutoff)
            ->delete();

        Log::info('Purged old pending Discord registrations', [
            'users' => $userCount,
            'applications' => $applicationCount,
            'days_old' => $this->daysOld,
        ]);
    }
}
