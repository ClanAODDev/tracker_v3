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

        Member::query()
            ->where('division_id', '>', 0)
            ->where(function ($query) {
                $query->where(fn ($query) => $query
                    ->where('platoon_id', '>', 0)
                    ->whereNotExists(fn (Builder $platoon) => $platoon
                        ->from('platoons')
                        ->whereColumn('platoons.id', 'members.platoon_id')
                        ->whereColumn('platoons.division_id', 'members.division_id')))
                    ->orWhere(fn ($query) => $query
                        ->where('squad_id', '>', 0)
                        ->whereNotExists(fn (Builder $squad) => $squad
                            ->from('squads')
                            ->whereColumn('squads.id', 'members.squad_id')
                            ->whereColumn('squads.platoon_id', 'members.platoon_id')));
            })
            ->update([
                'platoon_id' => 0,
                'squad_id'   => 0,
            ]);
    }
}
