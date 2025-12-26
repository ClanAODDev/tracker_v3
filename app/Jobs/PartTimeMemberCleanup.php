<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class PartTimeMemberCleanup implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        DB::transaction(function () {
            Member::query()
                ->whereNotNull('division_id')
                ->where('division_id', '>', 0)
                ->whereHas('partTimeDivisions')
                ->with('partTimeDivisions:id')
                ->chunk(100, function ($members) {
                    foreach ($members as $member) {
                        $ptIds = $member->partTimeDivisions->pluck('id')->all();

                        if (in_array($member->division_id, $ptIds, true)) {
                            $member->partTimeDivisions()->detach($member->division_id);
                        }
                    }
                });
        });
    }
}
