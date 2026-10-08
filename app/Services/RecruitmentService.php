<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\Position;
use App\Exceptions\RecruitmentFailedException;
use App\Jobs\SyncDiscordMember;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\RankAction;
use App\Models\Transfer;
use App\Models\Unit;
use App\Notifications\Channel\NotifyDivisionNewExternalRecruit;
use App\Notifications\Channel\NotifyDivisionNewMemberRecruited;
use Illuminate\Support\Facades\DB;

class RecruitmentService
{
    public function __construct(private MemberHandleService $handles = new MemberHandleService) {}

    /**
     * @throws RecruitmentFailedException
     */
    public function createMember(
        int $clanId,
        string $name,
        Division $division,
        int $rankId,
        int $platoonId,
        ?int $squadId,
        array $handles,
        Member $recruiter
    ): Member {
        $existing = Member::where('clan_id', $clanId)->first();

        if ($existing && ! $existing->hasNoDivision()) {
            throw new RecruitmentFailedException(
                'This member already exists in the Tracker. Use the transfer process to move them between divisions instead.'
            );
        }

        $platoon = Unit::query()->whereNull('parent_id')->where('division_id', $division->id)->find($platoonId);

        if (! $platoon) {
            throw new RecruitmentFailedException('Selected platoon does not belong to this division.');
        }

        $squad = $squadId ? $platoon->descendantsQuery()->find($squadId) : null;

        if ($squadId && ! $squad) {
            throw new RecruitmentFailedException('Selected unit does not belong to the selected platoon.');
        }

        return DB::transaction(function () use (
            $clanId,
            $name,
            $division,
            $rankId,
            $platoon,
            $squad,
            $handles,
            $recruiter
        ) {
            $member = Member::firstOrNew(['clan_id' => $clanId]);

            $member->fill([
                'name'                   => $name,
                'join_date'              => now(),
                'last_activity'          => now(),
                'recruiter_id'           => $recruiter->clan_id,
                'rank'                   => $rankId,
                'position'               => Position::MEMBER,
                'division_id'            => $division->id,
                'flagged_for_inactivity' => false,
                'last_promoted_at'       => now(),
                'unit_id'                => ($squad ?? $platoon)->id,
            ])->save();

            $this->handles->setForDivision($member, $division, $handles);

            $member->recordActivity(ActivityType::RECRUITED);

            RankAction::create([
                'member_id'     => $member->id,
                'rank'          => $rankId,
                'justification' => 'New recruit',
                'requester_id'  => $recruiter->id,
            ])->approveAndAccept();

            Transfer::create([
                'member_id'   => $member->id,
                'division_id' => $division->id,
                'approved_at' => now(),
            ]);

            return $member;
        });
    }

    public function createMemberRequest(Member $member, Division $division, Member $requester): void
    {
        if (MemberRequest::pending()->whereMemberId($member->id)->exists()) {
            return;
        }

        MemberRequest::create([
            'requester_id' => $requester->id,
            'member_id'    => $member->id,
            'division_id'  => $division->id,
        ]);
    }

    /**
     * Shared post-creation housekeeping for a freshly recruited member,
     * regardless of whether they came through the plain or Discord flow:
     * open the member request, notify the division, and queue a Discord sync.
     */
    public function finalizeRecruitment(Member $member, Division $division, Member $recruiter): void
    {
        $this->createMemberRequest($member, $division, $recruiter);

        $this->notifyDivisionOfRecruit($member, $division);

        SyncDiscordMember::dispatch($member);
    }

    private function notifyDivisionOfRecruit(Member $member, Division $division): void
    {
        if ($division->id !== auth()->user()->member->division_id) {
            $division->notify(new NotifyDivisionNewExternalRecruit($member, auth()->user()));

            return;
        }

        $division->notify(new NotifyDivisionNewMemberRecruited($member, auth()->user()));
    }
}
