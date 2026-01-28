<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\RankAction;
use App\Models\Transfer;
use App\Models\User;

class RecruitmentService
{
    public function __construct(
        protected ForumProcedureService $forumProcedureService
    ) {}

    public function createForumAccountForDiscordUser(User $user, string $forumName, int $recruiterId): array
    {
        if (! $user->date_of_birth) {
            return [
                'success' => false,
                'error' => 'Date of birth is required to create a forum account.',
            ];
        }

        if (! $user->forum_password) {
            return [
                'success' => false,
                'error' => 'Password is required to create a forum account.',
            ];
        }

        $forumService = app(AODForumService::class);
        $existingUser = $forumService->getUserByEmail($user->email);

        if ($existingUser) {
            $user->update(['forum_password' => null]);

            return [
                'success' => true,
                'clan_id' => (int) $existingUser->userid,
                'existing_account' => true,
            ];
        }

        $result = AODForumService::createForumUser(
            $recruiterId,
            $forumName,
            $user->email,
            $user->date_of_birth->format('Y-m-d'),
            $user->forum_password,
            $user->discord_id,
        );

        if (! $result['success']) {
            return $result;
        }

        $forumUser = $this->forumProcedureService->checkUser($forumName, $user->forum_password);

        $user->update(['forum_password' => null]);

        if (! $forumUser?->userid) {
            return [
                'success' => false,
                'error' => 'Forum account created but failed to retrieve user ID.',
            ];
        }

        return [
            'success' => true,
            'clan_id' => (int) $forumUser->userid,
        ];
    }

    public function createMember(
        int $clanId,
        string $name,
        Division $division,
        int $rankId,
        int $platoonId,
        ?int $squadId,
        ?string $ingameName,
        int $recruiterId
    ): Member {
        $member = Member::firstOrNew(['clan_id' => $clanId]);

        $member->fill([
            'name' => $name,
            'join_date' => now(),
            'last_activity' => now(),
            'recruiter_id' => $recruiterId,
            'rank' => $rankId,
            'position' => Position::MEMBER,
            'division_id' => $division->id,
            'flagged_for_inactivity' => false,
            'last_promoted_at' => now(),
            'platoon_id' => $platoonId,
            'squad_id' => $squadId ?? 0,
        ])->save();

        $this->attachIngameHandle($member, $division, $ingameName);

        $member->recordActivity(ActivityType::RECRUITED);

        RankAction::create([
            'member_id' => $member->id,
            'rank' => $rankId,
            'justification' => 'New recruit',
            'requester_id' => $recruiterId,
        ])->approveAndAccept();

        Transfer::create([
            'member_id' => $member->id,
            'division_id' => $division->id,
            'approved_at' => now(),
        ]);

        return $member;
    }

    private function attachIngameHandle(Member $member, Division $division, ?string $ingameName): void
    {
        if (! $ingameName || ! $division->handle_id) {
            return;
        }

        $member->handles()->syncWithoutDetaching([
            $division->handle_id => ['value' => $ingameName],
        ]);
    }

    public function createMemberRequest(Member $member, Division $division, int $requesterId): void
    {
        if (MemberRequest::pending()->whereMemberId($member->clan_id)->exists()) {
            return;
        }

        MemberRequest::create([
            'requester_id' => $requesterId,
            'member_id' => $member->clan_id,
            'division_id' => $division->id,
        ]);
    }
}
