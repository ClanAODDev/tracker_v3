<?php

namespace App\Jobs;

use App\Enums\Position;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
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
            $stalePlatoons = Platoon::query()
                ->where('leader_id', '>', 0)
                ->whereNotExists(fn (Builder $query) => $query
                    ->from('members')
                    ->whereColumn('members.clan_id', 'platoons.leader_id')
                    ->whereNull('members.deleted_at')
                    ->where('members.position', Position::PLATOON_LEADER->value)
                    ->whereColumn('members.platoon_id', 'platoons.id')
                    ->whereColumn('members.division_id', 'platoons.division_id'))
                ->pluck('id');

            $staleSquads = Squad::query()
                ->where('leader_id', '>', 0)
                ->whereNotExists(fn (Builder $query) => $query
                    ->from('members')
                    ->whereColumn('members.clan_id', 'squads.leader_id')
                    ->whereNull('members.deleted_at')
                    ->where('members.position', Position::SQUAD_LEADER->value)
                    ->whereColumn('members.squad_id', 'squads.id')
                    ->whereExists(fn (Builder $platoon) => $platoon
                        ->from('platoons')
                        ->whereColumn('platoons.id', 'squads.platoon_id')
                        ->whereColumn('platoons.division_id', 'members.division_id')))
                ->pluck('id');

            $units = app(UnitAssignment::class);

            Unit::query()
                ->where(fn ($query) => $query
                    ->where(fn ($q) => $q->where('legacy_type', Unit::LEGACY_PLATOON)->whereIn('legacy_id', $stalePlatoons))
                    ->orWhere(fn ($q) => $q->where('legacy_type', Unit::LEGACY_SQUAD)->whereIn('legacy_id', $staleSquads)))
                ->get()
                ->each(fn (Unit $unit) => $units->setLeader($unit, null, recordActivity: false));

            Member::unassignedSquadLeaders()
                ->update(['position' => Position::MEMBER]);

            Member::unassignedPlatoonLeaders()
                ->update(['position' => Position::MEMBER]);
        });
    }
}
