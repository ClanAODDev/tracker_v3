<?php

namespace App\Http\Controllers;

use App\Division;
use Illuminate\Http\Request;
use App\Repositories\ClanRepository;

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
        $censusCounts = $this->clan->censusCounts(30);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        // break down census data by division (latest)
        $divisionCensuses = Division::with('census')->get();

        // calculate graph area of active vs whole
        $divisionCensuses->each(function ($division) {
            $count = $division->census->first()->count;
            $weeklyActive = $division->census->first()->weekly_active_count;

            $division->popMinusActive = $count - $weeklyActive;
            $division->weeklyActive = $weeklyActive;
        });


        $rankDemographic = $this->clan->allRankDemographic();

        return view('statistics.show')->with(compact(
            'memberCount', 'previousCensus', 'lastYearCensus', 'memberCount',
            'divisionCensuses', 'rankDemographic'
        ));
    }
}
