<?php

namespace App\Http\Controllers;

use App\Division;
use App\Member;
use App\Repositories\ClanRepository;
use Carbon\Carbon;

class ClanStatisticsController extends Controller
{

    private $clan;

    public function __construct(ClanRepository $clanRepository)
    {
        $this->middleware('auth');

        $this->clan = $clanRepository;
    }

    public function show()
    {
        $memberCount = $this->clan->totalActiveMembers();

        // get our census information, and organize it
        $censusCounts = $this->clan->censusCounts();
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        // fetch all divisions and eager load census data
        $censuses = Division::active()->with('census')->get()
            // filter out divisions without census information
            ->filter(function ($division) {
                return count($division->census);
            })
            // calculate population and weekly active
            ->each(function ($division) {
                $division->total = $division->census->last()->count;
                $division->popMinusActive = $division->census->last()->count - $division->census->last()->weekly_active_count;
                $division->weeklyActive = $division->census->last()->weekly_active_count;
                $division->weeklyTsActive = $division->census->last()->weekly_ts_count;
            });

        $mismatchedTSMembers = $this->clan->teamspeakReport();

        // break down rank distribution
        $rankDemographic = collect($this->clan->allRankDemographic());
        $rankDemographic->each(function ($rank) use ($memberCount) {
            $rank->difference = $memberCount - $rank->count;
        });

        return view('statistics.show')->with(compact(
            'memberCount', 'previousCensus', 'lastYearCensus', 'memberCount',
            'censuses', 'rankDemographic', 'mismatchedTSMembers'
        ));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTsReport()
    {
        $invalidDates = function ($member) {
            return ! carbon_date_or_null_if_zero($member->last_ts_activity);
        };

        $newMembers = function ($member) {
            return $member->created_at < Carbon::now()->subDays(2);
        };

        $issues = Member::whereHas('division')
            ->with('rank', 'division')->get()
            ->filter($invalidDates)
            ->filter($newMembers);

        return view('statistics.ts-report', compact('issues'));
    }
}
