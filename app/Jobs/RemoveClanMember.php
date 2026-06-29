<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODForumService;
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

    public function handle(): void
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

        $context = $member
            ? ['name' => $member->name, 'groups' => $member->groups ?? []]
            : ['name' => 'unknown', 'groups' => []];

        Log::info('Removing forum member', array_merge(['clan_id' => $this->memberIdBeingRemoved], $context));

        try {
            AODForumService::removeForumMember(
                memberIdBeingRemoved: $this->memberIdBeingRemoved,
                impersonatingMemberId: $this->impersonatingMemberId,
            );
        } catch (RuntimeException $e) {
            $groupList = implode(', ', $context['groups']);
            throw new RuntimeException(
                "{$e->getMessage()} | name: {$context['name']}, groups: [{$groupList}]",
                previous: $e
            );
        }
    }
}
