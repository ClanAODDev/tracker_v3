<?php

namespace App\Console\Commands;

use App\Enums\Position;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UnitsPreflight extends BaseCommand
{
    protected $signature = 'tracker:units-preflight
                            {--json : Print the full report as JSON}
                            {--sample=10 : How many ids to show per check}';

    protected $description = 'Read-only audit of platoon/squad data ahead of the move to a unit tree';

    public function handle(): int
    {
        $report = collect($this->checks())->map(fn (array $check) => [
            'group'       => $check['group'],
            'key'         => $check['key'],
            'description' => $check['description'],
            'ids'         => $check['query']->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
        ]);

        if ($this->option('json')) {
            $this->line(json_encode($report->values()->all(), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $sample = (int) $this->option('sample');

        $this->table(
            ['Group', 'Check', 'Count', 'Sample ids'],
            $report->map(fn (array $row) => [
                $row['group'],
                $row['description'],
                count($row['ids']),
                implode(', ', array_slice($row['ids'], 0, $sample)) . (count($row['ids']) > $sample ? ', …' : ''),
            ])->all(),
        );

        $this->info('Read-only: nothing was changed.');

        return self::SUCCESS;
    }

    private function checks(): array
    {
        return [
            $this->check('units', 'orphan_squads', 'Squads whose platoon does not exist', DB::table('squads as s')
                ->leftJoin('platoons as p', 'p.id', '=', 's.platoon_id')
                ->whereNull('p.id')
                ->select('s.id')),
            $this->check('units', 'live_squads_under_deleted_platoons', 'Live squads under a soft-deleted platoon', DB::table('squads as s')
                ->join('platoons as p', 'p.id', '=', 's.platoon_id')
                ->whereNull('s.deleted_at')
                ->whereNotNull('p.deleted_at')
                ->select('s.id')),
            $this->check('units', 'platoons_without_division', 'Platoons whose division does not exist', DB::table('platoons as p')
                ->leftJoin('divisions as d', 'd.id', '=', 'p.division_id')
                ->whereNull('d.id')
                ->select('p.id')),
            $this->check('units', 'soft_deleted_platoons', 'Soft-deleted platoons (migrate as deleted)', DB::table('platoons')
                ->whereNotNull('deleted_at')
                ->select('id')),
            $this->check('units', 'soft_deleted_squads', 'Soft-deleted squads (migrate as deleted)', DB::table('squads')
                ->whereNotNull('deleted_at')
                ->select('id')),

            $this->check('members', 'missing_platoon', 'Members pointing at a platoon that does not exist', $this->members()
                ->leftJoin('platoons as p', 'p.id', '=', 'm.platoon_id')
                ->where('m.platoon_id', '>', 0)
                ->whereNull('p.id')),
            $this->check('members', 'missing_squad', 'Members pointing at a squad that does not exist', $this->members()
                ->leftJoin('squads as s', 's.id', '=', 'm.squad_id')
                ->where('m.squad_id', '>', 0)
                ->whereNull('s.id')),
            $this->check('members', 'platoon_in_other_division', 'Members whose platoon belongs to another division', $this->members()
                ->join('platoons as p', 'p.id', '=', 'm.platoon_id')
                ->whereColumn('p.division_id', '!=', 'm.division_id')),
            $this->check('members', 'squad_in_other_platoon', 'Members whose squad is not in their platoon', $this->members()
                ->join('squads as s', 's.id', '=', 'm.squad_id')
                ->where('m.platoon_id', '>', 0)
                ->whereColumn('s.platoon_id', '!=', 'm.platoon_id')),
            $this->check('members', 'squad_without_platoon', 'Members with a squad but no platoon', $this->members()
                ->where('m.squad_id', '>', 0)
                ->where(fn (Builder $q) => $q->whereNull('m.platoon_id')->orWhere('m.platoon_id', 0))),
            $this->check('members', 'in_deleted_unit', 'Members assigned to a soft-deleted platoon or squad', $this->members()
                ->leftJoin('platoons as p', 'p.id', '=', 'm.platoon_id')
                ->leftJoin('squads as s', 's.id', '=', 'm.squad_id')
                ->where(fn (Builder $q) => $q->whereNotNull('p.deleted_at')->orWhereNotNull('s.deleted_at'))),

            $this->check('leaders', 'leader_missing_member', 'Live units whose leader_id matches no current member (unit ids)', $this->liveUnits()
                ->leftJoin('members as m', fn ($join) => $join->on('m.clan_id', '=', 'u.leader_id')->whereNull('m.deleted_at'))
                ->whereNull('m.id')
                ->select('u.uid as id')),
            $this->check('leaders', 'leader_other_division', 'Unit leaders who belong to another division (member ids)', $this->liveUnits()
                ->join('members as m', fn ($join) => $join->on('m.clan_id', '=', 'u.leader_id')->whereNull('m.deleted_at'))
                ->whereColumn('m.division_id', '!=', 'u.division_id')
                ->select('m.id')),
            $this->check('leaders', 'leader_wrong_position', "Unit leaders whose position doesn't match the unit they lead (member ids)", $this->liveUnits()
                ->join('members as m', fn ($join) => $join->on('m.clan_id', '=', 'u.leader_id')->whereNull('m.deleted_at'))
                ->whereRaw('m.position != u.leader_position')
                ->select('m.id')),
            $this->check('leaders', 'leader_not_assigned_to_unit', 'Unit leaders not assigned to the unit they lead (member ids)', $this->liveUnits()
                ->join('members as m', fn ($join) => $join->on('m.clan_id', '=', 'u.leader_id')->whereNull('m.deleted_at'))
                ->whereRaw("(u.kind = 'platoon' and m.platoon_id != u.id) or (u.kind = 'squad' and m.squad_id != u.id)")
                ->select('m.id')),
            $this->check('leaders', 'position_without_unit', 'Members with a platoon/squad leader position who lead no live unit', $this->members()
                ->whereIn('m.position', [Position::PLATOON_LEADER->value, Position::SQUAD_LEADER->value])
                ->whereNotExists(fn ($q) => $q->from('platoons')->whereColumn('platoons.leader_id', 'm.clan_id')->whereNull('platoons.deleted_at')->where('m.position', Position::PLATOON_LEADER->value))
                ->whereNotExists(fn ($q) => $q->from('squads')->whereColumn('squads.leader_id', 'm.clan_id')->whereNull('squads.deleted_at')->where('m.position', Position::SQUAD_LEADER->value))),
            $this->check('leaders', 'leads_several_units', 'Members who lead more than one live unit', DB::query()
                ->fromSub($this->liveUnits()->select('u.leader_id'), 'l')
                ->join('members as m', fn ($join) => $join->on('m.clan_id', '=', 'l.leader_id')->whereNull('m.deleted_at'))
                ->groupBy('m.id')
                ->havingRaw('count(*) > 1')
                ->select('m.id')),

            $this->check('activity', 'activity_missing_subject', 'Activity rows pointing at a platoon/squad that does not exist', DB::table('activities as a')
                ->leftJoin('platoons as p', fn ($join) => $join->on('p.id', '=', 'a.subject_id')->where('a.subject_type', 'App\\Models\\Platoon'))
                ->leftJoin('squads as s', fn ($join) => $join->on('s.id', '=', 'a.subject_id')->where('a.subject_type', 'App\\Models\\Squad'))
                ->whereIn('a.subject_type', ['App\\Models\\Platoon', 'App\\Models\\Squad'])
                ->whereNull('p.id')
                ->whereNull('s.id')
                ->select('a.id')),
        ];
    }

    private function check(string $group, string $key, string $description, Builder $query): array
    {
        return compact('group', 'key', 'description', 'query');
    }

    private function members(): Builder
    {
        return DB::table('members as m')
            ->whereNull('m.deleted_at')
            ->where('m.division_id', '>', 0)
            ->select('m.id');
    }

    private function liveUnits(): Builder
    {
        $platoons = DB::table('platoons')
            ->whereNull('deleted_at')
            ->where('leader_id', '>', 0)
            ->selectRaw("id, id as uid, 'platoon' as kind, division_id, leader_id, ? as leader_position", [Position::PLATOON_LEADER->value]);

        $squads = DB::table('squads')
            ->join('platoons', 'platoons.id', '=', 'squads.platoon_id')
            ->whereNull('squads.deleted_at')
            ->where('squads.leader_id', '>', 0)
            ->selectRaw("squads.id, squads.id as uid, 'squad' as kind, platoons.division_id, squads.leader_id, ? as leader_position", [Position::SQUAD_LEADER->value]);

        return DB::query()->fromSub($platoons->unionAll($squads), 'u');
    }
}
