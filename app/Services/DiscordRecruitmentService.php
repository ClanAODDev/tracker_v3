<?php

namespace App\Services;

use App\Enums\ForumGroup;
use App\Exceptions\RecruitmentFailedException;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Log;

class DiscordRecruitmentService
{
    public function __construct(
        protected ForumProcedureService $procedureService,
        protected AODForumService $forumService,
        protected RecruitmentService $recruitmentService,
    ) {}

    /**
     * @throws RecruitmentFailedException
     */
    public function recruit(User $pendingUser, Request $request, Division $division, Member $recruiter): Member
    {
        Log::channel('recruiting')->info('Discord recruitment started', [
            'pending_user_id'  => $pendingUser->id,
            'discord_id'       => $pendingUser->discord_id,
            'discord_username' => $pendingUser->discord_username,
            'email'            => $pendingUser->email,
            'has_password'     => ! empty($pendingUser->forum_password),
            'recruiter_id'     => $recruiter->clan_id,
        ]);

        $forumUser = $this->resolveForumAccount($pendingUser, $request->forum_name, $recruiter->clan_id);
        $clanId    = (int) $forumUser->userid;

        $this->assertEligible($clanId);

        $this->syncDiscordInfo($clanId, $pendingUser);

        $member = $this->recruitmentService->createMember(
            $clanId,
            $request->forum_name,
            $division,
            (int) $request->rank,
            (int) $request->platoon,
            $request->squad ? (int) $request->squad : null,
            $request->ingame_name,
            $recruiter
        );

        Log::channel('recruiting')->info('Member created via Discord recruitment', [
            'member_id' => $member->id,
            'clan_id'   => $clanId,
        ]);

        $pendingUser->update(['member_id' => $member->id]);

        DivisionApplication::where('user_id', $pendingUser->id)->get()->each->delete();

        return $member;
    }

    private function resolveForumAccount(User $pendingUser, string $forumName, int $recruiterClanId): object
    {
        $forumUser = $this->forumService->getUserByEmail($pendingUser->email);

        Log::channel('recruiting')->info('Forum account lookup by email', [
            'found'   => $forumUser !== null,
            'user_id' => $forumUser?->userid,
        ]);

        if (! $forumUser && $pendingUser->forum_password) {
            Log::channel('recruiting')->info('No existing forum account — creating new account', [
                'forum_name' => $forumName,
            ]);

            $forumUser = $this->createForumAccount($pendingUser, $forumName, $recruiterClanId);

            Log::channel('recruiting')->info('Forum account creation result', [
                'success' => $forumUser !== null,
                'user_id' => $forumUser?->userid,
            ]);

            if (! $forumUser) {
                throw new RecruitmentFailedException(
                    'Failed to create forum account. Please try again or contact an administrator.'
                );
            }
        }

        if (! $forumUser) {
            Log::channel('recruiting')->warning('Discord recruitment aborted — no forum account and no password', [
                'pending_user_id' => $pendingUser->id,
            ]);

            throw new RecruitmentFailedException(
                'No forum account found for this user and no password is available to create one. '
                . 'The user may need to re-register through Discord.'
            );
        }

        return $forumUser;
    }

    private function createForumAccount(User $pendingUser, string $forumName, int $recruiterClanId): ?object
    {
        $result = AODForumService::createForumAccount(
            impersonatingMemberId: $recruiterClanId,
            username: $forumName,
            email: $pendingUser->email,
            dateOfBirth: $pendingUser->date_of_birth->format('Y-m-d'),
            password: $pendingUser->forum_password,
            discordId: $pendingUser->discord_id,
            forumGroup: ForumGroup::AWAITING_MODERATION,
        );

        if (! $result['success']) {
            Log::channel('recruiting')->warning('Forum account creation failed', [
                'error'   => $result['error'] ?? 'Unknown error',
                'payload' => [
                    'aod_userid'  => $recruiterClanId,
                    'username'    => $forumName,
                    'email'       => $pendingUser->email,
                    'dob'         => $pendingUser->date_of_birth->format('Y-m-d'),
                    'discord_id'  => $pendingUser->discord_id,
                    'usergroupid' => ForumGroup::AWAITING_MODERATION->value,
                ],
            ]);

            return null;
        }

        $pendingUser->update(['forum_password' => null]);

        return $this->forumService->getUserByEmail($pendingUser->email);
    }

    /**
     * @throws RecruitmentFailedException
     */
    private function assertEligible(int $clanId): void
    {
        $forumProfile = $this->procedureService->getUser($clanId);

        Log::channel('recruiting')->info('Forum profile fetched', [
            'clan_id'        => $clanId,
            'found'          => $forumProfile !== null,
            'usergroupid'    => $forumProfile?->usergroupid,
            'membergroupids' => $forumProfile->membergroupids ?? null,
        ]);

        if (! $forumProfile || ! property_exists($forumProfile, 'usergroupid')) {
            return;
        }

        $group = ForumGroup::tryFrom((int) $forumProfile->usergroupid);

        if ($group && ! $group->isEligibleForRecruitment()) {
            Log::channel('recruiting')->warning('Discord recruitment blocked — ineligible forum group', [
                'clan_id' => $clanId,
                'group'   => $group->name,
            ]);

            throw new RecruitmentFailedException($group->recruitmentRejectionReason());
        }
    }

    /**
     * @throws RecruitmentFailedException
     */
    private function syncDiscordInfo(int $clanId, User $pendingUser): void
    {
        $discordResult = $this->procedureService->setDiscordInfo(
            userId: $clanId,
            discordId: $pendingUser->discord_id,
            discordTag: $pendingUser->discord_username ?? '',
        );

        Log::channel('recruiting')->info('Set discord info on forum profile', [
            'clan_id'          => $clanId,
            'discord_id'       => $pendingUser->discord_id,
            'discord_username' => $pendingUser->discord_username,
            'rows_matched'     => $discordResult?->rows_matched,
            'rows_affected'    => $discordResult?->rows_affected,
        ]);

        if (! $discordResult || ! $discordResult->rows_matched) {
            Log::channel('recruiting')->error('Discord recruitment aborted — forum account not found', [
                'clan_id' => $clanId,
            ]);

            throw new RecruitmentFailedException('Forum account not found for this user. Please contact an administrator.');
        }
    }
}
