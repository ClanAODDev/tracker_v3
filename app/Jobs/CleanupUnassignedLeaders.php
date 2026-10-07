<?php

namespace App\Jobs;

use App\Enums\Position;
use App\Models\Member;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CleanupUnassignedLeaders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        DB::transaction(function () {
            $units = app(UnitAssignment::class);

            foreach ([Unit::LEGACY_PLATOON => Position::PLATOON_LEADER, Unit::LEGACY_SQUAD => Position::SQUAD_LEADER] as $type => $position) {
                Unit::query()
                    ->where('legacy_type', $type)
                    ->where('leader_id', '>', 0)
                    ->whereNotExists(fn (Builder $query) => $query
                        ->from('members')
                        ->whereColumn('members.clan_id', 'units.leader_id')
                        ->whereNull('members.deleted_at')
                        ->where('members.position', $position->value)
                        ->whereColumn('members.unit_id', 'units.id')
                        ->whereColumn('members.division_id', 'units.division_id'))
                    ->get()
                    ->each(fn (Unit $unit) => $units->setLeader($unit, null, recordActivity: false));
            }

            Member::unassignedSquadLeaders()
                ->update(['position' => Position::MEMBER]);

            Member::unassignedPlatoonLeaders()
                ->update(['position' => Position::MEMBER]);
        });
    }
}
