<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODForumService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\UnrecoverableJobException;
use Illuminate\Support\Facades\DB;

class RemoveClanMember implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        private readonly int $memberIdBeingRemoved,
        private readonly int $impersonatingMemberId,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $member = Member::withTrashed()->where('clan_id', $this->memberIdBeingRemoved)->first();

        if ($member) {
            $conflict = DB::connection('aod_forums')
                ->table('user')
                ->where('username', $member->name)
                ->where('userid', '!=', $this->memberIdBeingRemoved)
                ->exists();

            if ($conflict) {
                throw new UnrecoverableJobException(
                    "Cannot remove member {$this->memberIdBeingRemoved} ({$member->name}): " .
                    "username '{$member->name}' already exists for a different forum user. Manual intervention required."
                );
            }
        }

        AODForumService::removeForumMember(
            memberIdBeingRemoved: $this->memberIdBeingRemoved,
            impersonatingMemberId: $this->impersonatingMemberId,
        );
    }
}
