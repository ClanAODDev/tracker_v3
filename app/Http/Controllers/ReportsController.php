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
use Illuminate\View\View;

#[Middleware('auth')]
class ReportsController extends Controller
{
    public function __construct(private ClanRepository $clan) {}

    public function clanCensusReport(Request $request): View
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

        $censusCounts = $hasDateFilter
            ? $this->clan->censusCountsBetween($start, $end)
            : $defaultCensus;

        $memberCount    = $this->clan->totalActiveMembers();
        $previousCensus = $defaultCensus->first();

        $milestones = $this->clan->censusMilestones();

        $filteredCensus = $censusCounts->reverse()->values();

        $populations = $filteredCensus->map(fn ($row) => [
            Carbon::parse($row->date)->subDay()->valueOf(),
            (int) $row->count,
        ]);

        $weeklyVoiceActive = $filteredCensus->map(fn ($row) => [
            Carbon::parse($row->date)->subDay()->valueOf(),
            (int) $row->weekly_voice_active,
        ]);

        $censuses = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with(['census' => fn ($q) => $q->whereBetween(
                DB::raw('DATE(created_at)'),
                [$start, $end]
            )->orderBy('created_at')])
            ->get()
            ->filter(fn ($division) => $division->census->isNotEmpty())
            ->each(function ($division) {
                $latest                       = $division->census->last();
                $division->latestCensus       = $latest;
                $division->population         = $latest->count;
                $division->weeklyVoiceActive  = $latest->weekly_voice_count;
                $division->weeklyVoicePercent = $latest->count > 0
                    ? round($latest->weekly_voice_count / $latest->count * 100, 1)
                    : 0;
            });

        $totalPopulation  = $censuses->sum('population');
        $totalVoiceActive = $censuses->sum('weeklyVoiceActive');

        $rankDemographic = $this->clan->allRankDemographic()->map(function ($rank) use ($memberCount) {
            $rank->percent = $memberCount > 0 ? round($rank->count / $memberCount * 100, 1) : 0;

            return $rank;
        });

        $dateRange = ['start' => $start, 'end' => $end];

        return view('reports.clan-statistics', compact(
            'memberCount',
            'previousCensus',
            'filteredCensus',
            'censuses',
            'rankDemographic',
            'totalPopulation',
            'totalVoiceActive',
            'milestones',
            'populations',
            'weeklyVoiceActive',
            'dateRange',
            'hasDateFilter',
        ));
    }

    public function outstandingMembersReport(): View
    {
        $clanMax     = config('aod.maximum_days_inactive');
        $clanMaxDate = now()->subDays($clanMax)->format('Y-m-d');

        $divisions = Division::active()
            ->orderBy('name')
            ->withCount('members')
            ->get()
            ->each(function ($division) use ($clanMax, $clanMaxDate) {
                $divisionMax     = $division->settings()->get('inactivity_days') ?? $clanMax;
                $divisionMaxDate = now()->subDays($divisionMax)->format('Y-m-d');

                $baseQuery = $division->members()->whereDoesntHave('leave', fn ($q) => $q->whereDate('end_date', '>', today()));

                $division->divisionMax      = $divisionMax;
                $division->outstandingCount = (clone $baseQuery)->where('last_voice_activity', '<', $clanMaxDate)->count();
                $division->inactiveCount    = (clone $baseQuery)->where('last_voice_activity', '<', $divisionMaxDate)->count();
                $division->activeCount      = $division->members_count - $division->inactiveCount;
                $division->pctInactive      = $division->members_count > 0
                    ? round($division->inactiveCount / $division->members_count * 100, 1)
                    : 0;
                $division->pctOutstanding = $division->members_count > 0
                    ? round($division->outstandingCount / $division->members_count * 100, 1)
                    : 0;
            });

        $totals = (object) [
            'population'  => $divisions->sum('members_count'),
            'outstanding' => $divisions->sum('outstandingCount'),
            'inactive'    => $divisions->sum('inactiveCount'),
        ];
        $totals->pctOutstanding = $totals->population > 0 ? round($totals->outstanding / $totals->population * 100, 1) : 0;
        $totals->pctInactive    = $totals->population > 0 ? round($totals->inactive / $totals->population * 100, 1) : 0;

        return view('reports.outstanding-members', compact('divisions', 'totals', 'clanMax'));
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
            })->get();
            $sortedMembers = collect(Arr::sort($members, fn ($member) => $member->rank_id));
            $sortedMembers->each(function ($member) {
                echo $member->present()->rankName() . ", {$member->user->role->value}" . PHP_EOL;
            });
            echo '---------- END OF DIVISION ----------' . PHP_EOL . PHP_EOL . PHP_EOL;
        }
    }

    public function divisionTurnoverReport(): View
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
            ->each(function ($division) {
                $pop             = $division->members_count ?: 1;
                $division->pct30 = round($division->new_members_last30_count / $pop * 100, 1);
                $division->pct60 = round($division->new_members_last60_count / $pop * 100, 1);
                $division->pct90 = round($division->new_members_last90_count / $pop * 100, 1);
            });

        $totals = (object) [
            'population' => $divisions->sum('members_count'),
            'last30'     => $divisions->sum('new_members_last30_count'),
            'last60'     => $divisions->sum('new_members_last60_count'),
            'last90'     => $divisions->sum('new_members_last90_count'),
        ];

        $totalPop      = $totals->population ?: 1;
        $totals->pct30 = round($totals->last30 / $totalPop * 100, 1);
        $totals->pct60 = round($totals->last60 / $totalPop * 100, 1);
        $totals->pct90 = round($totals->last90 / $totalPop * 100, 1);

        return view('reports.division-turnover', compact('divisions', 'totals'));
    }

    public function leadership(): View
    {
        $divisions = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with([
                'sergeants' => function ($query) {
                    $query
                        ->orderByRaw('
                    CASE
                        WHEN position = ? THEN 9999
                        ELSE -position
                    END ASC
                ', [Position::CLAN_ADMIN->value])
                        ->orderByDesc('rank');
                },
            ])
            ->withCount('sgtAndSsgt')
            ->get();

        $leadership = Member::query()
            ->with('division')
            ->where('rank', '>', Rank::STAFF_SERGEANT)
            ->where('division_id', '!=', 0)
            ->orderByDesc('rank')
            ->orderBy('name')
            ->get();

        return view('reports.leadership', compact('divisions', 'leadership'));
    }
}
