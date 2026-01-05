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
use Illuminate\Support\Facades\Log;

class RecruitmentService
{
    public function __construct(
        protected ForumProcedureService $forumService
    ) {}

    public function createForumAccountForDiscordUser(User $user, string $forumName): array
    {
        $formattedName = 'AOD_' . $forumName;

        Log::info('RecruitmentService: Creating forum account for Discord user', [
            'user_id' => $user->id,
            'discord_id' => $user->discord_id,
            'discord_username' => $user->discord_username,
            'email' => $user->email,
            'forum_name' => $formattedName,
        ]);

        // @todo Implement actual forum account creation via stored procedure
        // Expected procedure: create_user(email, username) returns { userid, error }
        //
        // $result = $this->forumService->createUser($user->email, $formattedName);
        //
        // if ($result && $result->error) {
        //     return [
        //         'success' => false,
        //         'error' => $result->error,
        //     ];
        // }
        //
        // if ($result && $result->userid) {
        //     return [
        //         'success' => true,
        //         'clan_id' => (int) $result->userid,
        //     ];
        // }

        return [
            'success' => false,
            'error' => 'Forum account creation is not yet implemented.',
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
