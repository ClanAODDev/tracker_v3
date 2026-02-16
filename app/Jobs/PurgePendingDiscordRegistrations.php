<?php

namespace App\Jobs;

use App\Models\DivisionApplication;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Kirschbaum\Commentions\Comment;

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

        Comment::where('commentable_type', (new DivisionApplication)->getMorphClass())
            ->whereIn('commentable_id', $applicationIds)
            ->delete();

        $applicationCount = $applicationIds->count();
        DivisionApplication::whereIn('id', $applicationIds)->delete();

        $userCount = User::pendingDiscord()
            ->where('created_at', '<', $cutoff)
            ->delete();

        Log::info('Purged old pending Discord registrations', [
            'users'        => $userCount,
            'applications' => $applicationCount,
            'days_old'     => $this->daysOld,
        ]);
    }
}
