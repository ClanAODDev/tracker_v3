<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Queue\Queueable;

class ResetOrphanedUnitAssignments implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Member::query()
            ->whereNotNull('unit_id')
            ->where(function ($query) {
                $query->whereNull('division_id')
                    ->orWhere('division_id', 0);
            })
            ->update(['unit_id' => null]);

        Member::query()
            ->whereNotNull('unit_id')
            ->whereNotExists(fn (Builder $unit) => $unit
                ->from('units')
                ->whereColumn('units.id', 'members.unit_id')
                ->whereColumn('units.division_id', 'members.division_id')
                ->whereNull('units.deleted_at'))
            ->update(['unit_id' => null]);
    }
}
