<?php

namespace App\Jobs;

use App\AOD\Traits\Procedureable;
use App\Models\Transfer;
use App\Traits\RetryableJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateDivisionForMember implements ShouldQueue
{
    use Procedureable;
    use Queueable;
    use RetryableJob;

    const PROCEDURE_SET_DIVISION = 'set_user_division';

    /**
     * Create a new job instance.
     */
    public function __construct(public Transfer $transfer) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->callProcedure(self::PROCEDURE_SET_DIVISION, [
            $this->transfer->member->clan_id,
            $this->transfer->division->name,
        ]);
    }
}
