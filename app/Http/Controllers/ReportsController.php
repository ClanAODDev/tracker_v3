<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Enums\Rank;
use App\Exceptions\FactoryMissingException;
use App\Models\Division;
use App\Models\Member;
use App\Repositories\ClanRepository;
use Illuminate\Support\Arr;

class ReportsController extends Controller
{
    public function __construct(private ClanRepository $clan)
    {
        $this->middleware('auth');
    }

    public function clanCensusReport()
    {
        $censusCounts = $this->clan->censusCounts();

        if ($censusCounts->isEmpty()) {
            throw new FactoryMissingException('You might need to run the `census` factory');
        }

        $memberCount = $this->clan->totalActiveMembers();
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $censuses = Division::active()
            ->orderBy('name')
            ->withoutFloaters()
            ->with('census')
            ->get()
            ->filter(fn ($division) => $division->census->isNotEmpty())
            ->each(function ($division) {
                $latest = $division->census->last();
                $division->latestCensus = $latest;
                $division->population = $latest->count;
                $division->weeklyVoiceActive = $latest->weekly_voice_count;
                $division->weeklyVoicePercent = $latest->count > 0
                    ? round($latest->weekly_voice_count / $latest->count * 100, 1)
                    : 0;
            });

        $totalPopulation = $censuses->sum('population');
        $totalVoiceActive = $censuses->sum('weeklyVoiceActive');

        $rankDemographic = $this->clan->allRankDemographic()->map(function ($rank) use ($memberCount) {
            $rank->percent = $memberCount > 0 ? round($rank->count / $memberCount * 100, 1) : 0;

            return $rank;
        });

        return view('reports.clan-statistics', compact(
            'memberCount',
            'previousCensus',
            'lastYearCensus',
            'censuses',
            'rankDemographic',
            'totalPopulation',
            'totalVoiceActive',
        ));
    }

    public function outstandingMembersReport()
    {
        $clanMax = config('aod.maximum_days_inactive');
        $clanMaxDate = now()->subDays($clanMax)->format('Y-m-d');

        $divisions = Division::active()
            ->orderBy('name')
            ->withCount('members')
            ->get()
            ->each(function ($division) use ($clanMax, $clanMaxDate) {
                $divisionMax = $division->settings()->get('inactivity_days') ?? $clanMax;
                $divisionMaxDate = now()->subDays($divisionMax)->format('Y-m-d');

                $baseQuery = $division->members()->whereDoesntHave('leave', fn ($q) => $q->whereDate('end_date', '>', today()));

                $division->divisionMax = $divisionMax;
                $division->outstandingCount = (clone $baseQuery)->where('last_voice_activity', '<', $clanMaxDate)->count();
                $division->inactiveCount = (clone $baseQuery)->where('last_voice_activity', '<', $divisionMaxDate)->count();
                $division->activeCount = $division->members_count - $division->inactiveCount;
                $division->pctInactive = $division->members_count > 0
                    ? round($division->inactiveCount / $division->members_count * 100, 1)
                    : 0;
                $division->pctOutstanding = $division->members_count > 0
                    ? round($division->outstandingCount / $division->members_count * 100, 1)
                    : 0;
            });

        $totals = (object) [
            'population' => $divisions->sum('members_count'),
            'outstanding' => $divisions->sum('outstandingCount'),
            'inactive' => $divisions->sum('inactiveCount'),
        ];
        $totals->pctOutstanding = $totals->population > 0 ? round($totals->outstanding / $totals->population * 100, 1) : 0;
        $totals->pctInactive = $totals->population > 0 ? round($totals->inactive / $totals->population * 100, 1) : 0;

        return view('reports.outstanding-members', compact('divisions', 'totals', 'clanMax'));
    }

    /**
     * Users with empty discord tag.
     */
    public function usersWithoutDiscordReport()
    {
        $divisions = Division::active()->get();
        $data = [];
        foreach ($divisions as $division) {
            foreach ($division->members->where('discord', '') as $member) {
                $data[$division->name][] = [$member->clan_id => "{$member->name}"];
            }
        }

        return $data;
    }

    public function divisionUsersWithAccess()
    {
        foreach (Division::active()->get() as $division) {
            echo '---------- ' . $division->name . ' ---------- ' . PHP_EOL;
            $members = $division->members()->whereHas('user', function ($query) {
                $query->where('role_id', '>', 2);
            })->get();
            $sortedMembers = collect(Arr::sort($members, fn ($member) => $member->rank_id));
            $sortedMembers->each(function ($member) {
                echo $member->present()->rankName() . ", {$member->user->role_id}" . PHP_EOL;
            });
            echo '---------- END OF DIVISION ----------' . PHP_EOL . PHP_EOL . PHP_EOL;
        }
    }

    public function divisionTurnoverReport()
    {
        $divisions = Division::active()
            ->orderBy('name')
            ->withCount('members', 'newMembersLast30', 'newMembersLast60', 'newMembersLast90')
            ->get()
            ->each(function ($division) {
                $pop = $division->members_count ?: 1;
                $division->pct30 = round($division->new_members_last30_count / $pop * 100, 1);
                $division->pct60 = round($division->new_members_last60_count / $pop * 100, 1);
                $division->pct90 = round($division->new_members_last90_count / $pop * 100, 1);
            });

        $totals = (object) [
            'population' => $divisions->sum('members_count'),
            'last30' => $divisions->sum('new_members_last30_count'),
            'last60' => $divisions->sum('new_members_last60_count'),
            'last90' => $divisions->sum('new_members_last90_count'),
        ];

        $totalPop = $totals->population ?: 1;
        $totals->pct30 = round($totals->last30 / $totalPop * 100, 1);
        $totals->pct60 = round($totals->last60 / $totalPop * 100, 1);
        $totals->pct90 = round($totals->last90 / $totalPop * 100, 1);

        return view('reports.division-turnover', compact('divisions', 'totals'));
    }

    /**
     * @return Factory|View
     */
    public function leadership()
    {
        $divisions = Division::active()
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
            ->where('rank', '>', Rank::STAFF_SERGEANT)
            ->where('division_id', '!=', 0)
            ->orderByDesc('rank')
            ->orderBy('name')
            ->get();

        return view('reports.leadership', compact('divisions', 'leadership'));
    }
}
