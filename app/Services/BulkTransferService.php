<?php

namespace App\Services;

use App\Jobs\UpdateDivisionForMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use App\Notifications\Channel\NotifyDivisionBulkMemberTransfer;
use Illuminate\Support\Collection;

class BulkTransferService
{
    /**
     * Move the given members to a division, creating and approving a Transfer
     * record for each and notifying the affected divisions in batches.
     *
     * @param  Collection<int, Member>  $members
     * @return int number of members actually moved
     */
    public function transfer(Collection $members, Division $targetDivision): int
    {
        $groupedBySourceDivision = collect();

        foreach ($members as $member) {
            if ($member->division_id === $targetDivision->id) {
                continue;
            }

            $sourceDivision = $member->division;

            $transfer = Transfer::create([
                'member_id'   => $member->id,
                'division_id' => $targetDivision->id,
            ]);

            $transfer->approve();

            UpdateDivisionForMember::dispatch($transfer);

            $key   = $sourceDivision?->id ?? 0;
            $group = $groupedBySourceDivision->get($key, [
                'division' => $sourceDivision,
                'members'  => collect(),
            ]);
            $group['members']->push($member);
            $groupedBySourceDivision->put($key, $group);
        }

        foreach ($groupedBySourceDivision as $group) {
            $sourceDivision = $group['division'];
            $movedMembers   = $group['members'];

            $sourceDivision?->notify(new NotifyDivisionBulkMemberTransfer(
                $movedMembers,
                $targetDivision->name,
                NotifyDivisionBulkMemberTransfer::TYPE_OUTGOING,
            ));

            $targetDivision->notify(new NotifyDivisionBulkMemberTransfer(
                $movedMembers,
                $sourceDivision?->name ?? 'no division',
                NotifyDivisionBulkMemberTransfer::TYPE_INCOMING,
            ));
        }

        return $groupedBySourceDivision->sum(fn (array $group) => $group['members']->count());
    }
}
