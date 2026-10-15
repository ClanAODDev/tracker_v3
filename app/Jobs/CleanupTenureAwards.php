<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use App\Notifications\DM\NotifyMemberAwardReceived;
use App\Services\TenureAwardService;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            $this->notifyHighestTiers($stats['granted']);
        }

        Log::info('Tenure awards reconciled', [
            'granted'             => $stats['missing'],
            'flagged_for_removal' => $stats['invalid'],
        ]);
    }

    private function notifyHighestTiers(Collection $granted): void
    {
        $granted
            ->groupBy('member_id')
            ->map(fn (Collection $awards) => $awards->sortByDesc(
                fn (MemberAward $award) => array_search($award->award_id, TenureAwardService::AWARD_IDS, strict: true),
            )->first())
            ->each(fn (MemberAward $memberAward) => $this->notify($memberAward));
    }

    private function notify(MemberAward $memberAward): void
    {
        $memberAward->loadMissing(['member.division', 'award']);

        $member   = $memberAward->member;
        $division = $member->division;

        if ($division?->settings()->get('chat_alerts.member_awarded')) {
            $division->notify(new NotifyDivisionMemberAwarded($member->name, $memberAward->award));
        }

        $member->notify(new NotifyMemberAwardReceived($memberAward->award->name));
    }
}
