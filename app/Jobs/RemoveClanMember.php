<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\AODForumService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveClanMember implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $memberIdBeingRemoved,
        private readonly int $impersonatingMemberId,
    ) {}

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        AODForumService::removeForumMember(
            memberIdBeingRemoved: $this->memberIdBeingRemoved,
            impersonatingMemberId: $this->impersonatingMemberId,
        );
    }
}
