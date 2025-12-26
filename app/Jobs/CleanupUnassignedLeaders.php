<?php

namespace App\Jobs;

use App\Enums\Position;
use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CleanupUnassignedLeaders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        DB::transaction(function () {
            Member::unassignedSquadLeaders()
                ->update(['position' => Position::MEMBER]);

            Member::unassignedPlatoonLeaders()
                ->update(['position' => Position::MEMBER]);
        });
    }
}
