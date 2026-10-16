<?php

namespace App\Services;

use App\Models\MemberAward;
use App\Notifications\Channel\NotifyDivisionMemberAwarded;
use App\Notifications\Channel\NotifyDivisionMemberAwardsBulk;
use App\Notifications\DM\NotifyMemberAwardReceived;
use App\Notifications\DM\NotifyRequesterAwardApproved;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class AwardNotificationService
{
    public function notifyApproved(MemberAward $memberAward): void
    {
        $memberAward->loadMissing('member.division', 'award', 'requester');

        $division = $memberAward->member->division;

        if ($division?->settings()->get('chat_alerts.member_awarded')) {
            $division->notify(new NotifyDivisionMemberAwarded($memberAward->member->name, $memberAward->award));
        }

        $this->notifyRecipient($memberAward);

        if ($memberAward->requester && $memberAward->requester_id !== $memberAward->member_id) {
            $memberAward->requester->notify(new NotifyRequesterAwardApproved($memberAward));
        }
    }

    /**
     * @param  Collection<int, MemberAward>  $memberAwards
     */
    public function notifyRecipients(Collection $memberAwards): void
    {
        $memberAwards->each(fn (MemberAward $memberAward) => $this->notifyRecipient($memberAward));
    }

    private function notifyRecipient(MemberAward $memberAward): void
    {
        $memberAward->loadMissing('member', 'award');

        $memberAward->member->notify(new NotifyMemberAwardReceived($memberAward->award->name));
    }

    /**
     * Notify each affected division of its approved awards, consolidated into
     * one message per division regardless of how many award types or members
     * were included in the batch.
     *
     * @param  EloquentCollection<int, MemberAward>  $memberAwards
     */
    public function notifyBulkApproval(EloquentCollection $memberAwards): void
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
