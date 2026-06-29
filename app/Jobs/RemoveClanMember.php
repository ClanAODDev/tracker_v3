<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODForumService;
use App\Services\ForumProcedureService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\UnrecoverableJobException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RemoveClanMember implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly int $memberIdBeingRemoved,
        private readonly int $impersonatingMemberId,
    ) {}

    public function handle(ForumProcedureService $forum): void
    {
        $member = Member::withTrashed()->where('clan_id', $this->memberIdBeingRemoved)->first();

        if ($member) {
            if (AODForumService::hasForumUsernameConflict($member->clan_id, $member->name)) {
                throw new UnrecoverableJobException(
                    "Cannot remove member {$this->memberIdBeingRemoved} ({$member->name}): " .
                    "username '{$member->name}' already exists for a different forum user. Manual intervention required."
                );
            }
        }

        $forumUser = $forum->getUser($this->memberIdBeingRemoved);

        $context = [
            'clan_id'         => $this->memberIdBeingRemoved,
            'name'            => $member?->name ?? 'unknown',
            'usergroupid'     => $forumUser?->usergroupid ?? null,
            'membergroupids'  => $forumUser?->membergroupids ?? null,
        ];

        Log::info('Removing forum member', $context);

        try {
            AODForumService::removeForumMember(
                memberIdBeingRemoved: $this->memberIdBeingRemoved,
                impersonatingMemberId: $this->impersonatingMemberId,
            );
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                "{$e->getMessage()} | name: {$context['name']}, usergroupid: {$context['usergroupid']}, membergroupids: {$context['membergroupids']}",
                previous: $e
            );
        }
    }
}
