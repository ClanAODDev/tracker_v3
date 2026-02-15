<?php

namespace App\Jobs;

use App\Enums\ForumGroup;
use App\Models\Member;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

class AddClanMember implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Member $member,
        private readonly int $admin_id
    ) {}

    public function handle(ForumProcedureService $procedureService): void
    {
        try {
            AODForumService::addForumMember(
                impersonatingMemberId: $this->admin_id,
                memberIdBeingAdded: $this->member->clan_id,
                rank: $this->member->rank->getLabel(),
                name: 'AOD_' . $this->member->name,
                division: $this->member->division->name
            );
        } catch (RuntimeException $e) {
            $reason = $this->diagnoseFailure($procedureService);

            throw new RuntimeException(
                $e->getMessage() . ($reason ? " (Reason: {$reason})" : ''),
                previous: $e
            );
        }

        SyncDiscordMember::dispatch($this->member);
    }

    private function diagnoseFailure(ForumProcedureService $procedureService): ?string
    {
        $profile = $procedureService->getUser($this->member->clan_id);

        if (! $profile || ! property_exists($profile, 'usergroupid')) {
            return 'Forum account not found';
        }

        $group = ForumGroup::tryFrom((int) $profile->usergroupid);

        return match ($group) {
            ForumGroup::AWAITING_EMAIL_CONFIRMATION => 'User has not verified their email',
            ForumGroup::AWAITING_MODERATION => 'User is awaiting moderation approval',
            ForumGroup::BANNED => 'User forum account is banned',
            ForumGroup::MEMBER => 'User is already an AOD member',
            null => "Unknown forum group: {$profile->usergroupid}",
            default => "User is in forum group: {$group->name}",
        };
    }
}
