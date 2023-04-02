<?php

namespace App\Http\Controllers;

use App\Enums\Position;
use App\Exceptions\FactoryMissingException;
use App\Models\Division;
use App\Models\Member;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class ReportsController extends \App\Http\Controllers\Controller
{
    public function __construct(\App\Repositories\ClanRepository $clanRepository)
    {
        $this->middleware('auth');
        $this->clan = $clanRepository;
    }

    /**
     * @return Factory|View
     */
    public function clanCensusReport()
    {
        $memberCount = $this->clan->totalActiveMembers();

        if (!$this->clan->censusCounts()->count()) {
            throw new FactoryMissingException('You might need to run the `census` factory');
        }

        // get our census information, and organize it
        $censusCounts = $this->clan->censusCounts();
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        // fetch all divisions and eager load census data
        $censuses = Division::active()->orderBy('name')->with('census')->get()->filter(fn (
            $division
        ) => \count($division->census))->each(function ($division) {
            $division->total = $division->census->last()->count;
            $division->popMinusActive = $division->census->last()->count - $division->census->last()->weekly_ts_count;
            $division->weeklyTsActive = $division->census->last()->weekly_ts_count;
        });

        $mismatchedTSMembers = $this->clan->teamspeakReport();

        // break down rank distribution
        $rankDemographic = collect($this->clan->allRankDemographic());
        $rankDemographic->each(function ($rank) use ($memberCount) {
            $rank->difference = $memberCount - $rank->count;
        });

        return view('reports.clan-statistics')->with(compact(
            'memberCount',
            'previousCensus',
            'lastYearCensus',
            'memberCount',
            'censuses',
            'rankDemographic',
            'mismatchedTSMembers'
        ));
    }

    /**
     * @return Factory|View
     */
    public function clanTsReport()
    {
        $invalidDates = fn ($member) => !carbon_date_or_null_if_zero($member->last_ts_activity);
        $newMembers = fn ($member) => $member->created_at < \Carbon\Carbon::now()->subDays(2);

        $issues = Member::whereHas('division')->with(
            'rank',
            'division'
        )->get()->filter($invalidDates)->filter($newMembers);

        return view('reports.clan-ts-report', compact('issues'));
    }

    /**
     * @return Factory|View
     */
    public function outstandingMembersReport()
    {
        $clanMax = config('app.aod.maximum_days_inactive');
        $divisions = Division::active()->orderBy('name')->withCount('members')->get();
        $divisions->map(function ($division) use ($clanMax) {
            $divisionMax = $division->settings()->get('inactivity_days');
            $members = $division->members()->whereDoesntHave('leave')->get();
            $outstandingCount = $members->where(
                'last_ts_activity',
                '<',
                \Carbon\Carbon::now()->subDays($clanMax)->format('Y-m-d')
            )->count();
            $inactiveCount = $members->where(
                'last_ts_activity',
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
        $divisions = Division::active()->withCount(
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
        $divisions = Division::active()->with([
            'sergeants' => function ($query) {
                $query->orderByDesc('rank_id')->orWhereIn('position', [
                    Position::EXECUTIVE_OFFICER,
                    Position::COMMANDING_OFFICER,
                ]);
            }, 'sergeants.rank',
        ])->withCount('sgtAndSsgt')->get();

        $leadership = Member::where('rank_id', '>', 10)
            ->where('division_id', '!=', 0)
            ->with('rank')
            ->orderByDesc('rank_id')
            ->orderBy('name')->get();

        return view('reports.leadership', compact('divisions', 'leadership'));
    }
}
