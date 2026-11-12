<?php

namespace App\Jobs;

use App\Enums\ForumGroup;
use App\Models\Member;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
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
        $this->member->loadMissing('division');

        if (! $this->member->division) {
            throw new RuntimeException("Member {$this->member->clan_id} has no division assigned.");
        }

        if ($this->member->discord_id && $this->member->discord) {
            $procedureService->setDiscordInfo(
                userId: $this->member->clan_id,
                discordId: $this->member->discord_id,
                discordTag: $this->member->discord,
            );
        }

        if ($this->alreadyInClan($procedureService)) {
            Log::info('Skipping forum add, member already in an AOD group', [
                'clan_id' => $this->member->clan_id,
            ]);

            SyncDiscordMember::dispatch($this->member);

            return;
        }

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

    private function alreadyInClan(ForumProcedureService $procedureService): bool
    {
        $profile = $procedureService->getUser($this->member->clan_id);

        if (! $profile || ! property_exists($profile, 'usergroupid')) {
            return false;
        }

        return ForumGroup::tryFrom((int) $profile->usergroupid)?->isClanMember() ?? false;
    }

    private function diagnoseFailure(ForumProcedureService $procedureService): ?string
    {
        $profile = $procedureService->getUser($this->member->clan_id);

        if (! $profile || ! property_exists($profile, 'usergroupid')) {
            return 'Forum account not found';
        }

        $group = ForumGroup::tryFrom((int) $profile->usergroupid);

        if (! $group) {
            return "Unknown forum group: {$profile->usergroupid}";
        }

        return $group->recruitmentRejectionReason() ?? "User is in forum group: {$group->name}";
    }
}
