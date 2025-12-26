<?php

namespace App\Jobs;

use App\Models\Transfer;
use App\Services\ForumProcedureService;
use App\Traits\RetryableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateDivisionForMember implements ShouldQueue
{
    use Queueable;
    use RetryableJob;

    public function __construct(
        public Transfer $transfer
    ) {}

    public function handle(ForumProcedureService $procedureService): void
    {
        $procedureService->setUserDivision(
            $this->transfer->member->clan_id,
            $this->transfer->division->name
        );
    }
}
