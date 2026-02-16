<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ResetOrphanedUnitAssignments implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Member::query()
            ->where(function ($query) {
                $query->whereNull('division_id')
                    ->orWhere('division_id', 0);
            })
            ->where(function ($query) {
                $query->where('platoon_id', '>', 0)
                    ->orWhere('squad_id', '>', 0);
            })
            ->update([
                'platoon_id' => 0,
                'squad_id'   => 0,
            ]);
    }
}
