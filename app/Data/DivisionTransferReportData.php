<?php

namespace App\Data;

use App\Enums\ActivityType;
use App\Models\Division;
use App\Models\Member;
use App\Support\DateRangeRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DivisionTransferReportData
{
    private array $range;

    private Collection $sources;

    private array $stats;

    public function __construct(private Division $division)
    {
        [$start, $end, $this->range] = DateRangeRequest::parse();

        $sources = DateRangeRequest::applyTo(
            DB::table('activities as a')
                ->leftJoin('divisions as d', 'd.id', '=', 'a.division_id')
                ->join('members as m', 'm.id', '=', 'a.subject_id')
                ->where('a.subject_type', Member::class)
                ->where('a.name', ActivityType::TRANSFERRED->value)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(a.properties, '$.to_division')) = ?", [$division->name])
                ->where('m.division_id', $division->id)
                ->whereNull('m.deleted_at')
                ->select('a.division_id', 'd.name as division_name', DB::raw('COUNT(*) as count'))
                ->groupBy('a.division_id', 'd.name')
                ->orderByDesc('count'),
            $start,
            $end,
            'a.created_at',
        )->get();

        $recruitedCount = DateRangeRequest::applyTo(
            DB::table('activities as a')
                ->join('members as m', 'm.id', '=', 'a.subject_id')
                ->where('a.subject_type', Member::class)
                ->where('a.name', ActivityType::RECRUITED->value)
                ->where('a.division_id', $division->id)
                ->where('m.division_id', $division->id)
                ->whereNull('m.deleted_at'),
            $start,
            $end,
            'a.created_at',
        )->count();

        $total = $sources->sum('count') + $recruitedCount;

        $sources = $sources->map(fn ($row) => [
            'name'        => $row->division_name ?? 'Unknown',
            'count'       => (int) $row->count,
            'percentage'  => $total > 0 ? round($row->count / $total * 100, 1) : 0,
            'is_original' => false,
        ]);

        if ($recruitedCount > 0) {
            $sources->push([
                'name'        => 'Started here',
                'count'       => $recruitedCount,
                'percentage'  => $total > 0 ? round($recruitedCount / $total * 100, 1) : 0,
                'is_original' => true,
            ]);
        }

        $this->sources = $sources;

        $this->stats = [
            'total'         => $total,
            'sourceCount'   => $sources->where('is_original', false)->count(),
            'transferredIn' => $sources->where('is_original', false)->sum('count'),
            'startedHere'   => $sources->where('is_original', true)->sum('count'),
        ];
    }

    public static function for(Division $division): self
    {
        return new self($division);
    }

    public function toArray(): array
    {
        return [
            'division' => ['name' => $this->division->name, 'slug' => $this->division->slug],
            'range'    => $this->range,
            'stats'    => $this->stats,
            'sources'  => $this->sources->values(),
        ];
    }
}
