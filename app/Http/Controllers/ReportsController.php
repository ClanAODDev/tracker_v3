<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Enums\Rank;
use App\Exceptions\FactoryMissingException;
use App\Models\Division;
use App\Repositories\ClanRepository;

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
        $clanMax = config('app.aod.maximum_days_inactive');
        $divisions = \App\Models\Division::active()->orderBy('name')->withCount('members')->get();
        $divisions->map(function ($division) use ($clanMax) {
            $divisionMax = $division->settings()->get('inactivity_days');
            $members = $division->members()->whereDoesntHave('leave', function ($q) {
                $q->whereDate('end_date', '>', today());
            })->get();
            $outstandingCount = $members->where(
                'last_voice_activity',
                '<',
                \Carbon\Carbon::now()->subDays($clanMax)->format('Y-m-d')
            )->count();
            $inactiveCount = $members->where(
                'last_voice_activity',
                '<',
                \Carbon\Carbon::now()->subDays($divisionMax)->format('Y-m-d')
            )->count();
            $division->outstanding_members = $outstandingCount;
            $division->inactive_members = $inactiveCount;
            $division->percent_inactive = number_format($inactiveCount / max($division->members_count, 1) * 100, 1);

            return $division;
        });

        return view('reports.outstanding-members', compact('divisions'));
    }

    /**
     * Users with empty discord tag.
     */
    public function usersWithoutDiscordReport()
    {
        $divisions = \App\Models\Division::active()->get();
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
        foreach (\App\Models\Division::active()->get() as $division) {
            echo '---------- ' . $division->name . ' ---------- ' . PHP_EOL;
            $members = $division->members()->whereHas('user', function ($query) {
                $query->where('role_id', '>', 2);
            })->get();
            $sortedMembers = collect(\Illuminate\Support\Arr::sort($members, fn ($member) => $member->rank_id));
            $sortedMembers->each(function ($member) {
                echo $member->present()->rankName() . ", {$member->user->role_id}" . PHP_EOL;
            });
            echo '---------- END OF DIVISION ----------' . PHP_EOL . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * @return mixed
     */
    public function divisionTurnoverReport()
    {
        $divisions = \App\Models\Division::active()->withCount(
            'members',
            'newMembersLast30',
            'newMembersLast60',
            'newMembersLast90'
        )->get();

        return view('reports.division-turnover', compact('divisions'));
    }

    /**
     * @return Factory|View
     */
    public function leadership()
    {
        $divisions = \App\Models\Division::active()
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

        $leadership = \App\Models\Member::query()
            ->where('rank', '>', Rank::STAFF_SERGEANT)
            ->where('division_id', '!=', 0)
            ->orderByDesc('rank')
            ->orderBy('name')
            ->get();

        return view('reports.leadership', compact('divisions', 'leadership'));
    }
}
