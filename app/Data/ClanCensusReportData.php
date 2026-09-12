<?php

namespace App\Data;

use App\Exceptions\FactoryMissingException;
use App\Models\Division;
use App\Repositories\ClanRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClanCensusReportData
{
    private string $start;

    private string $end;

    private bool $hasDateFilter;

    private Collection $chart;

    private Collection $censusTable;

    private Collection $divisions;

    private array $stats;

    private Collection $rankDemographic;

    public function __construct(Request $request, ClanRepository $clan)
    {
        $defaultStart = now()->subWeeks(52)->format('Y-m-d');
        $defaultEnd   = now()->format('Y-m-d');

        $this->start = $request->filled('start') ? $request->input('start') : $defaultStart;
        $this->end   = $request->filled('end') ? $request->input('end') : $defaultEnd;

        $this->hasDateFilter = $request->filled('start') || $request->filled('end');

        $defaultCensus = $clan->censusCounts(52);

        if ($defaultCensus->isEmpty()) {
            throw new FactoryMissingException('You might need to run the `census` factory');
        }

        $censusCounts   = $this->hasDateFilter ? $clan->censusCountsBetween($this->start, $this->end) : $defaultCensus;
        $memberCount    = $clan->totalActiveMembers();
        $previousCensus = $defaultCensus->first();
        $milestones     = $clan->censusMilestones();

        $rows = $censusCounts->reverse()->values();

        $this->chart = $rows->map(fn ($row) => [
            'date'       => Carbon::parse($row->date)->format('M j, y'),
            'population' => (int) $row->count,
            'voice'      => (int) $row->weekly_voice_active,
        ]);

        $table             = $rows->reverse()->values();
        $this->censusTable = $table->map(function ($row, $index) use ($table) {
            $prev  = $table->get($index + 1);
            $count = (int) $row->count;
            $voice = (int) $row->weekly_voice_active;

            return [
                'date'         => Carbon::parse($row->date)->format('M j, Y'),
                'population'   => $count,
                'change'       => $prev ? $count - (int) $prev->count : 0,
                'voiceActive'  => $voice,
                'voicePercent' => $count > 0 ? round($voice / $count * 100, 1) : 0,
            ];
        });

        $this->divisions = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with(['census' => fn ($q) => $q->whereBetween(DB::raw('DATE(created_at)'), [$this->start, $this->end])->orderBy('created_at')])
            ->get()
            ->filter(fn ($division) => $division->census->isNotEmpty())
            ->map(function ($division) {
                $latest  = $division->census->last();
                $percent = $latest->count > 0 ? round($latest->weekly_voice_count / $latest->count * 100, 1) : 0;

                return [
                    'name'         => $division->name,
                    'slug'         => $division->slug,
                    'population'   => $latest->count,
                    'voiceActive'  => $latest->weekly_voice_count,
                    'voicePercent' => $percent,
                ];
            })
            ->values();

        $totalPopulation  = $this->divisions->sum('population');
        $totalVoiceActive = $this->divisions->sum('voiceActive');

        $this->stats = [
            'memberCount'   => $memberCount,
            'previousCount' => $previousCensus?->count ? (int) $previousCensus->count : null,
            'firstCensus'   => $milestones->first ? [
                'total' => (int) $milestones->first->total,
                'date'  => Carbon::parse($milestones->first->date)->format('M j, Y'),
            ] : null,
            'peakCensus' => $milestones->peak ? [
                'total' => (int) $milestones->peak->total,
                'date'  => Carbon::parse($milestones->peak->date)->format('M j, Y'),
            ] : null,
            'totalPopulation'  => $totalPopulation,
            'totalVoiceActive' => $totalVoiceActive,
            'totalPercent'     => $totalPopulation > 0 ? round($totalVoiceActive / $totalPopulation * 100, 1) : 0,
        ];

        $this->rankDemographic = $clan->allRankDemographic()->map(fn ($rank) => [
            'abbreviation' => $rank->abbreviation,
            'count'        => (int) $rank->count,
            'percent'      => $memberCount > 0 ? round($rank->count / $memberCount * 100, 1) : 0,
        ])->values();
    }

    public static function for(Request $request, ClanRepository $clan): self
    {
        return new self($request, $clan);
    }

    public function toArray(): array
    {
        return [
            'stats' => [
                'memberCount'   => $this->stats['memberCount'],
                'previousCount' => $this->stats['previousCount'],
                'firstCensus'   => $this->stats['firstCensus'],
                'peakCensus'    => $this->stats['peakCensus'],
            ],
            'chart'               => $this->chart,
            'censusTable'         => $this->censusTable,
            'divisionPopulations' => [
                'rows'             => $this->divisions,
                'totalPopulation'  => $this->stats['totalPopulation'],
                'totalVoiceActive' => $this->stats['totalVoiceActive'],
                'totalPercent'     => $this->stats['totalPercent'],
            ],
            'rankDemographic' => $this->rankDemographic,
            'dateRange'       => ['start' => $this->start, 'end' => $this->end],
            'hasDateFilter'   => $this->hasDateFilter,
        ];
    }
}
