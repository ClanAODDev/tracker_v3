<?php

namespace App\Jobs;

use App\Models\Member;
use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use App\Notifications\DM\NotifyMemberAwardReceived;
use App\Services\TenureAwardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CleanupTenureAwards implements ShouldQueue
{
    use Queueable;

    public function handle(TenureAwardService $tenureAwards): void
    {
        $stats = $tenureAwards->reconcile(
            Member::query()->whereHas('division'),
            persist: true,
            removeInvalid: false,
        );

        $stats['granted']
            ->groupBy('member_id')
            ->map(fn ($awards) => $awards->sortByDesc(fn (MemberAward $award) => array_search($award->award_id, TenureAwardService::AWARD_IDS, strict: true))->first())
            ->each(fn (MemberAward $memberAward) => $this->notify($memberAward));

        Log::info('Tenure awards reconciled', [
            'granted'             => $stats['missing'],
            'flagged_for_removal' => $stats['invalid'],
        ]);
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
