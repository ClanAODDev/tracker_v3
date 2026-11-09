<?php

namespace App\Jobs;

use App\Models\Division;
use App\Models\Member;
use App\Notifications\Channel\NotifyDivisionForumRemovalFailed;
use App\Notifications\DM\NotifyRemoverForumRemovalFailed;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class RemoveClanMember implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly int $memberIdBeingRemoved,
        private readonly int $impersonatingMemberId,
        private readonly ?int $divisionId = null,
    ) {}

    public function handle(ForumProcedureService $forum): void
    {
        $member = Member::withTrashed()->where('clan_id', $this->memberIdBeingRemoved)->first();

        if ($member && $blocker = AODForumService::removalBlocker($member)) {
            $this->fail("Cannot remove member {$this->memberIdBeingRemoved} ({$member->name}): {$blocker}");

            return;
        }

        $forumUser = $forum->getUser($this->memberIdBeingRemoved);

        $context = [
            'clan_id'        => $this->memberIdBeingRemoved,
            'name'           => $member?->name ?? 'unknown',
            'usergroupid'    => $forumUser?->usergroupid ?? null,
            'membergroupids' => $forumUser?->membergroupids ?? null,
        ];

        Log::info('Removing forum member', $context);

        try {
            AODForumService::removeForumMember(
                memberIdBeingRemoved: $this->memberIdBeingRemoved,
                impersonatingMemberId: $this->impersonatingMemberId,
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'invalid_user_specified')) {
                // The forum's removeMember() returns this same response for two
                // different reasons: no forum profile at all, or a profile that
                // exists but is no longer in the AOD usergroup. Either way there's
                // nothing left to remove, so this isn't worth retrying.
                $reason = $context['usergroupid']
                    ? "exists on the forum but is not in the AOD usergroup (usergroupid: {$context['usergroupid']})"
                    : 'has no forum profile';

                Log::warning(
                    "Forum rejected removal of member {$this->memberIdBeingRemoved} as invalid_user_specified — member {$reason}, already effectively removed",
                    $context
                );

                return;
            }

            throw new RuntimeException(
                "{$e->getMessage()} | name: {$context['name']}, usergroupid: {$context['usergroupid']}, membergroupids: {$context['membergroupids']}",
                previous: $e
            );
        }
    }

    public function failed(?Throwable $exception): void
    {
        $remover    = Member::where('clan_id', $this->impersonatingMemberId)->first();
        $member     = Member::withTrashed()->where('clan_id', $this->memberIdBeingRemoved)->first();
        $division   = $this->divisionId ? Division::find($this->divisionId) : null;
        $memberName = $member?->name ?? (string) $this->memberIdBeingRemoved;
        $reason     = $exception?->getMessage() ?? 'Unknown error';

        if ($division) {
            $division->notify(new NotifyDivisionForumRemovalFailed(
                memberName: $memberName,
                clanId: $this->memberIdBeingRemoved,
                removerName: $remover?->name,
                reason: $reason,
            ));

            return;
        }

        $remover?->notify(new NotifyRemoverForumRemovalFailed(
            memberName: $memberName,
            clanId: $this->memberIdBeingRemoved,
            reason: $reason,
        ));
    }
}
