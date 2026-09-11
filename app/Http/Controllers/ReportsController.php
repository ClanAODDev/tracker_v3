<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Enums\Rank;
use App\Exceptions\FactoryMissingException;
use App\Models\Division;
use App\Models\Member;
use App\Repositories\ClanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

#[Middleware('auth')]
class ReportsController extends Controller
{
    public function __construct(private ClanRepository $clan) {}

    public function clanCensusReport(Request $request): Response
    {
        $defaultStart = now()->subWeeks(52)->format('Y-m-d');
        $defaultEnd   = now()->format('Y-m-d');

        $start = $request->filled('start') ? $request->input('start') : $defaultStart;
        $end   = $request->filled('end') ? $request->input('end') : $defaultEnd;

        $hasDateFilter = $request->filled('start') || $request->filled('end');

        $defaultCensus = $this->clan->censusCounts(52);

        if ($defaultCensus->isEmpty()) {
            throw new FactoryMissingException('You might need to run the `census` factory');
        }

        $censusCounts   = $hasDateFilter ? $this->clan->censusCountsBetween($start, $end) : $defaultCensus;
        $memberCount    = $this->clan->totalActiveMembers();
        $previousCensus = $defaultCensus->first();
        $milestones     = $this->clan->censusMilestones();

        $rows = $censusCounts->reverse()->values();

        $chart = $rows->map(fn ($row) => [
            'date'       => Carbon::parse($row->date)->format('M j, y'),
            'population' => (int) $row->count,
            'voice'      => (int) $row->weekly_voice_active,
        ]);

        $table       = $rows->reverse()->values();
        $censusTable = $table->map(function ($row, $index) use ($table) {
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

        $divisions = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with(['census' => fn ($q) => $q->whereBetween(DB::raw('DATE(created_at)'), [$start, $end])->orderBy('created_at')])
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

        $totalPopulation  = $divisions->sum('population');
        $totalVoiceActive = $divisions->sum('voiceActive');

        $rankDemographic = $this->clan->allRankDemographic()->map(fn ($rank) => [
            'abbreviation' => $rank->abbreviation,
            'count'        => (int) $rank->count,
            'percent'      => $memberCount > 0 ? round($rank->count / $memberCount * 100, 1) : 0,
        ])->values();

        return Inertia::render('reports/clan-census', [
            'stats' => [
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
            ],
            'chart'               => $chart,
            'censusTable'         => $censusTable,
            'divisionPopulations' => [
                'rows'             => $divisions,
                'totalPopulation'  => $totalPopulation,
                'totalVoiceActive' => $totalVoiceActive,
                'totalPercent'     => $totalPopulation > 0 ? round($totalVoiceActive / $totalPopulation * 100, 1) : 0,
            ],
            'rankDemographic' => $rankDemographic,
            'dateRange'       => ['start' => $start, 'end' => $end],
            'hasDateFilter'   => $hasDateFilter,
        ]);
    }

    public function outstandingMembersReport(): Response
    {
        $clanMax          = config('aod.maximum_days_inactive');
        $clanMaxThreshold = now()->subDays($clanMax)->startOfDay();

        $divisions = Division::active()->orderBy('name')->withCount('members')->get();

        // One query for every division's eligible members instead of two `count()`
        // queries per division — the per-division inactivity threshold is then
        // applied in memory below.
        $eligibleMembers = Member::query()
            ->whereIn('division_id', $divisions->pluck('id'))
            ->whereDoesntHave('leave', fn ($q) => $q->whereDate('end_date', '>', today()))
            ->get(['division_id', 'last_voice_activity'])
            ->groupBy('division_id');

        $divisions = $divisions
            ->map(function ($division) use ($clanMax, $clanMaxThreshold, $eligibleMembers) {
                $divisionMax       = $division->settings()->get('inactivity_days') ?? $clanMax;
                $divisionThreshold = now()->subDays($divisionMax)->startOfDay();
                $members           = $eligibleMembers->get($division->id, collect());

                $outstanding = $members->filter(fn ($m) => $m->last_voice_activity?->lt($clanMaxThreshold))->count();
                $inactive    = $members->filter(fn ($m) => $m->last_voice_activity?->lt($divisionThreshold))->count();
                $population  = $division->members_count;

                return [
                    'name'           => $division->name,
                    'slug'           => $division->slug,
                    'population'     => $population,
                    'divisionMax'    => $divisionMax,
                    'outstanding'    => $outstanding,
                    'inactive'       => $inactive,
                    'active'         => $population - $inactive,
                    'pctOutstanding' => $population > 0 ? round($outstanding / $population * 100, 1) : 0,
                    'pctInactive'    => $population > 0 ? round($inactive / $population * 100, 1) : 0,
                    'inactiveUrl'    => route('division.inactive-members', $division),
                ];
            });

        $population  = $divisions->sum('population');
        $outstanding = $divisions->sum('outstanding');
        $inactive    = $divisions->sum('inactive');

        return Inertia::render('reports/outstanding', [
            'clanMax'   => $clanMax,
            'divisions' => $divisions->values(),
            'totals'    => [
                'population'     => $population,
                'outstanding'    => $outstanding,
                'inactive'       => $inactive,
                'active'         => $population - $inactive,
                'pctOutstanding' => $population > 0 ? round($outstanding / $population * 100, 1) : 0,
                'pctInactive'    => $population > 0 ? round($inactive / $population * 100, 1) : 0,
                'pctActive'      => $population > 0 ? round(($population - $inactive) / $population * 100, 1) : 0,
            ],
        ]);
    }

    public function usersWithoutDiscordReport(): JsonResponse
    {
        $divisions = Division::active()->orderBy('name')->with('members')->get();
        $data      = [];
        foreach ($divisions as $division) {
            foreach ($division->members->where('discord', '') as $member) {
                $data[$division->name][] = [$member->clan_id => "{$member->name}"];
            }
        }

        return response()->json($data);
    }

    public function divisionUsersWithAccess(): void
    {
        foreach (Division::active()->orderBy('name')->get() as $division) {
            echo '---------- ' . $division->name . ' ---------- ' . PHP_EOL;
            $members = $division->members()->whereHas('user', function ($query) {
                $query->where('role', '>', 2);
            })->with('user')->get();
            $sortedMembers = collect(Arr::sort($members, fn ($member) => $member->rank_id));
            $sortedMembers->each(function ($member) {
                echo $member->present()->rankName() . ", {$member->user->role->value}" . PHP_EOL;
            });
            echo '---------- END OF DIVISION ----------' . PHP_EOL . PHP_EOL . PHP_EOL;
        }
    }

    public function divisionTurnoverReport(): Response
    {
        $divisions = Division::active()
            ->orderBy('name')
            ->withCount([
                'members',
                'members as new_members_last30_count' => fn ($q) => $q->where('join_date', '>', now()->subDays(30)),
                'members as new_members_last60_count' => fn ($q) => $q->where('join_date', '>', now()->subDays(60)),
                'members as new_members_last90_count' => fn ($q) => $q->where('join_date', '>', now()->subDays(90)),
            ])
            ->get()
            ->map(function ($division) {
                $pop = $division->members_count ?: 1;

                return [
                    'name'       => $division->name,
                    'population' => $division->members_count,
                    'last30'     => $division->new_members_last30_count,
                    'last60'     => $division->new_members_last60_count,
                    'last90'     => $division->new_members_last90_count,
                    'pct30'      => round($division->new_members_last30_count / $pop * 100, 1),
                    'pct60'      => round($division->new_members_last60_count / $pop * 100, 1),
                    'pct90'      => round($division->new_members_last90_count / $pop * 100, 1),
                ];
            });

        $population = $divisions->sum('population');
        $totalPop   = $population ?: 1;
        $last30     = $divisions->sum('last30');
        $last60     = $divisions->sum('last60');
        $last90     = $divisions->sum('last90');

        return Inertia::render('reports/turnover', [
            'divisions' => $divisions->values(),
            'totals'    => [
                'population' => $population,
                'last30'     => $last30,
                'last60'     => $last60,
                'last90'     => $last90,
                'pct30'      => round($last30 / $totalPop * 100, 1),
                'pct60'      => round($last60 / $totalPop * 100, 1),
                'pct90'      => round($last90 / $totalPop * 100, 1),
            ],
        ]);
    }

    public function leadership(): Response
    {
        $divisions = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with([
                'sergeants' => function ($query) {
                    $query
                        ->orderByRaw('CASE WHEN position = ? THEN 9999 ELSE -position END ASC', [Position::CLAN_ADMIN->value])
                        ->orderByDesc('rank');
                },
            ])
            ->withCount(['sgtAndSsgt', 'members'])
            ->get()
            ->map(fn ($division) => [
                'name'         => $division->name,
                'abbreviation' => $division->abbreviation,
                'logo'         => $division->getLogoPath(),
                'memberCount'  => $division->members_count,
                'sgtCount'     => $division->sgt_and_ssgt_count,
                'sgtRatio'     => ratio($division->sgt_and_ssgt_count, $division->members_count),
                'sergeants'    => $division->sergeants->map(fn (Member $member) => $this->leadershipRow($member))->values(),
            ]);

        $leadership = Member::query()
            ->with('division')
            ->where('rank', '>', Rank::STAFF_SERGEANT)
            ->where('division_id', '!=', 0)
            ->orderByDesc('rank')
            ->orderBy('name')
            ->get()
            ->map(fn (Member $member) => $this->leadershipRow($member));

        return Inertia::render('reports/leadership', [
            'clanLeadership' => $leadership,
            'divisions'      => $divisions->values(),
        ]);
    }

    private function leadershipRow(Member $member): array
    {
        $position = $member->position;

        return [
            'name'         => $member->present()->rankName(),
            'profileUrl'   => route('member', $member->getUrlParams()),
            'position'     => $position?->getAbbreviation() ?: 'SGT',
            'positionKind' => $position === Position::CLAN_ADMIN ? 'admin' : ($position?->name ? strtolower($position->name) : 'member'),
            'positionSort' => $position === Position::CLAN_ADMIN ? 0 : ($position?->value ?? 0),
            'lastPromoted' => $member->last_promoted_at?->format('Y-m-d'),
            'lastTrained'  => $member->last_trained_at?->format('Y-m-d'),
        ];
    }
}
