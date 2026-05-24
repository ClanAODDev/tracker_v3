<?php

namespace App\Jobs;

use App\Models\DivisionApplication;
use App\Models\Member;
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

        $staleApplicationIds = DivisionApplication::where('created_at', '<', $cutoff)
            ->pluck('id');

        Comment::where('commentable_type', (new DivisionApplication)->getMorphClass())
            ->whereIn('commentable_id', $staleApplicationIds)
            ->delete();

        $applicationCount = $staleApplicationIds->count();
        DivisionApplication::whereIn('id', $staleApplicationIds)->delete();

        $staleUserCount = User::pendingDiscord()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $recruitedMemberDiscordIds = Member::where('division_id', '!=', 0)
            ->whereNotNull('discord_id')
            ->whereHas('memberRequest', fn ($q) => $q->whereNotNull('approved_at'))
            ->pluck('discord_id');

        $matchedUsers = User::pendingDiscord()
            ->whereIn('discord_id', $recruitedMemberDiscordIds)
            ->get();

        $matchedUserIds = $matchedUsers->pluck('id');

        $matchedApplicationIds = DivisionApplication::whereIn('user_id', $matchedUserIds)->pluck('id');

        Comment::where('commentable_type', (new DivisionApplication)->getMorphClass())
            ->whereIn('commentable_id', $matchedApplicationIds)
            ->delete();

        DivisionApplication::whereIn('id', $matchedApplicationIds)->delete();

        $matchedUserCount = User::whereIn('id', $matchedUserIds)->delete();

        Log::info('Purged old pending Discord registrations', [
            'stale_users'        => $staleUserCount,
            'stale_applications' => $applicationCount,
            'matched_users'      => $matchedUserCount,
            'days_old'           => $this->daysOld,
        ]);
    }
}
