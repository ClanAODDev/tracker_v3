<?php

namespace App\Console\Commands;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitsVerify extends BaseCommand
{
    protected $signature = 'tracker:units-verify
                            {--json : Print the full report as JSON}
                            {--sample=10 : How many ids to show per check}';

    protected $description = 'Check that units and member unit assignments match platoons and squads exactly';

    public function handle(): int
    {
        $report = collect($this->checks())->map(fn (array $check) => [
            'key'         => $check['key'],
            'description' => $check['description'],
            'blocking'    => $check['blocking'],
            'ids'         => $check['ids'],
        ]);

        if ($this->option('json')) {
            $this->line(json_encode($report->values()->all(), JSON_PRETTY_PRINT));
        } else {
            $sample = (int) $this->option('sample');

            $this->table(['Check', 'Blocking', 'Count', 'Sample ids'], $report->map(fn (array $row) => [
                $row['description'],
                $row['blocking'] ? 'yes' : 'no',
                count($row['ids']),
                implode(', ', array_slice($row['ids'], 0, $sample)) . (count($row['ids']) > $sample ? ', …' : ''),
            ])->all());
        }

        $failures = $report->filter(fn (array $row) => $row['blocking'] && $row['ids'] !== []);

        if ($this->option('json')) {
            return $failures->isEmpty() ? self::SUCCESS : self::FAILURE;
        }

        if ($failures->isNotEmpty()) {
            $this->error('Units do not match platoons and squads.');

            return self::FAILURE;
        }

        $this->info('Units match platoons and squads.');

        return self::SUCCESS;
    }

    private function checks(): array
    {
        return [
            $this->check('platoons_without_unit', 'Platoons with no unit', true, DB::table('platoons as p')
                ->whereNotExists(fn ($q) => $q->from('units')->where('legacy_type', Unit::LEGACY_PLATOON)->whereColumn('units.legacy_id', 'p.id'))
                ->pluck('p.id')),
            $this->check('squads_without_unit', 'Squads with no unit', true, DB::table('squads as s')
                ->whereNotExists(fn ($q) => $q->from('units')->where('legacy_type', Unit::LEGACY_SQUAD)->whereColumn('units.legacy_id', 's.id'))
                ->pluck('s.id')),
            $this->check('units_without_source', 'Units with no platoon or squad behind them (unit ids)', true, DB::table('units as u')
                ->leftJoin('platoons as p', fn ($j) => $j->on('p.id', '=', 'u.legacy_id')->where('u.legacy_type', Unit::LEGACY_PLATOON))
                ->leftJoin('squads as s', fn ($j) => $j->on('s.id', '=', 'u.legacy_id')->where('u.legacy_type', Unit::LEGACY_SQUAD))
                ->whereNull('p.id')
                ->whereNull('s.id')
                ->pluck('u.id')),
            $this->check('platoon_units_differ', 'Platoon units whose division, name, leader, or archived state differ (platoon ids)', true, DB::table('platoons as p')
                ->join('units as u', fn ($j) => $j->on('u.legacy_id', '=', 'p.id')->where('u.legacy_type', Unit::LEGACY_PLATOON))
                ->where(fn ($q) => $q
                    ->where('u.depth', '!=', 1)
                    ->orWhereNotNull('u.parent_id')
                    ->orWhereRaw('not (u.division_id <=> p.division_id) and exists (select 1 from divisions d where d.id = p.division_id)')
                    ->orWhereRaw('not (u.name <=> p.name)')
                    ->orWhereRaw('not (u.leader_id <=> nullif(p.leader_id, 0))')
                    ->orWhereRaw('(u.deleted_at is null) != (p.deleted_at is null)'))
                ->pluck('p.id')),
            $this->check('squad_units_differ', 'Squad units whose parent, division, name, leader, or archived state differ (squad ids)', true, DB::table('squads as s')
                ->join('units as u', fn ($j) => $j->on('u.legacy_id', '=', 's.id')->where('u.legacy_type', Unit::LEGACY_SQUAD))
                ->leftJoin('platoons as p', 'p.id', '=', 's.platoon_id')
                ->leftJoin('units as parent', 'parent.id', '=', 'u.parent_id')
                ->where(fn ($q) => $q
                    ->where('u.depth', '!=', 2)
                    ->orWhereRaw('not (parent.legacy_id <=> p.id)')
                    ->orWhereRaw('not (u.division_id <=> p.division_id) and p.id is not null')
                    ->orWhereRaw('not (u.name <=> s.name)')
                    ->orWhereRaw('not (u.leader_id <=> nullif(s.leader_id, 0))')
                    ->orWhereRaw('(u.deleted_at is null) != (s.deleted_at is null and p.deleted_at is null and p.id is not null)'))
                ->pluck('s.id')),
            $this->check('bad_paths', 'Units whose path does not end with their id under their parent (unit ids)', true, DB::table('units as u')
                ->leftJoin('units as parent', 'parent.id', '=', 'u.parent_id')
                ->whereRaw("u.path != if(parent.id is null, concat('/', u.id, '/'), concat(parent.path, u.id, '/'))")
                ->pluck('u.id')),
            $this->check('members_unit_mismatch', "Members whose unit doesn't resolve to their platoon and squad", true, DB::table('members as m')
                ->join('units as u', 'u.id', '=', 'm.unit_id')
                ->leftJoin('units as parent', 'parent.id', '=', 'u.parent_id')
                ->where(fn ($q) => $q
                    ->whereRaw("u.legacy_type = 'squad' and not (u.legacy_id = m.squad_id and parent.legacy_id = m.platoon_id)")
                    ->orWhereRaw("u.legacy_type = 'platoon' and not (u.legacy_id = m.platoon_id and m.squad_id = 0)")
                    ->orWhereRaw('u.division_id != m.division_id')
                    ->orWhereNotNull('u.deleted_at'))
                ->pluck('m.id')),
            $this->check('consistent_members_without_unit', 'Members correctly assigned to a live platoon/squad but with no unit', true, $consistent = $this->unassignedMembers()
                ->whereExists(fn ($q) => $q->from('platoons as p')
                    ->whereColumn('p.id', 'm.platoon_id')
                    ->whereColumn('p.division_id', 'm.division_id')
                    ->whereNull('p.deleted_at'))
                ->where(fn ($q) => $q
                    ->where('m.squad_id', 0)
                    ->orWhereExists(fn ($s) => $s->from('squads as s')
                        ->whereColumn('s.id', 'm.squad_id')
                        ->whereColumn('s.platoon_id', 'm.platoon_id')
                        ->whereNull('s.deleted_at')))
                ->pluck('m.id')),
            $this->check('inconsistent_members_without_unit', 'Members with an inconsistent platoon/squad and no unit (the weekly job resets these)', false, $this->unassignedMembers()
                ->pluck('m.id')
                ->diff($consistent)),
        ];
    }

    private function unassignedMembers()
    {
        return DB::table('members as m')
            ->whereNull('m.deleted_at')
            ->whereNull('m.unit_id')
            ->where(fn ($q) => $q->where('m.platoon_id', '>', 0)->orWhere('m.squad_id', '>', 0));
    }

    private function check(string $key, string $description, bool $blocking, $ids): array
    {
        return ['key' => $key, 'description' => $description, 'blocking' => $blocking, 'ids' => $ids->map(fn ($id) => (int) $id)->unique()->values()->all()];
    }
}
