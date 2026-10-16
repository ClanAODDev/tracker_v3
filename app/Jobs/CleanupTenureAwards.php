<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\MemberAward;
use App\Services\AwardNotificationService;
use App\Services\TenureAwardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CleanupTenureAwards implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly bool $silent = false) {}

    public function handle(TenureAwardService $tenureAwards): void
    {
        $stats = $tenureAwards->reconcile(
            Member::query()->whereHas('division'),
            persist: true,
            removeInvalid: false,
        );

        if (! $this->silent) {
            $this->notifyHighestTiers($stats->granted);
        }

        Log::info('Tenure awards reconciled', [
            'granted'             => $stats->missing,
            'flagged_for_removal' => $stats->invalid,
        ]);
    }

    private function notifyHighestTiers(Collection $granted): void
    {
        $highest = new EloquentCollection(
            $granted
                ->groupBy('member_id')
                ->map(fn (Collection $awards) => $awards->sortByDesc(
                    fn (MemberAward $award) => array_search($award->award_id, TenureAwardService::AWARD_IDS, strict: true),
                )->first())
                ->values()
                ->all(),
        );

        $notifications = app(AwardNotificationService::class);

        $notifications->notifyRecipients($highest);
        $notifications->notifyBulkApproval($highest);
    }
}
