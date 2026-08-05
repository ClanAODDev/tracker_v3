<?php

namespace App\Services;

use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwardsBulk;
use Illuminate\Support\Collection;

class AwardNotificationService
{
    /**
     * Notify each affected division of its approved awards, consolidated into
     * one message per division regardless of how many award types or members
     * were included in the batch.
     *
     * @param  Collection<int, MemberAward>  $memberAwards
     */
    public function notifyBulkApproval(Collection $memberAwards): void
    {
        $memberAwards->load('member.division', 'award');

        $memberAwards
            ->filter(fn (MemberAward $memberAward) => $memberAward->member->division?->settings()->get('chat_alerts.member_awarded'))
            ->groupBy(fn (MemberAward $memberAward) => $memberAward->member->division_id)
            ->each(function (Collection $divisionAwards) {
                $division = $divisionAwards->first()->member->division;

                $division->notify(new NotifyDivisionMemberAwardsBulk($divisionAwards));
            });
    }
}
