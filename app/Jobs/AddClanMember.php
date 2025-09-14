<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODForumService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AddClanMember implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly Member $member,
        private readonly int $admin_id
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        AODForumService::addForumMember(
            impersonatingMemberId: $this->admin_id,
            memberIdBeingAdded: $this->member->clan_id,
            rank: $this->member->rank->getLabel(),
            name: 'AOD_' . $this->member->name,
            division: $this->member->division->name
        );
    }
}
