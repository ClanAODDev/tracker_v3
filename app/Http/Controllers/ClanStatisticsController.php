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
        $censusCounts = $this->clan->censusCounts(30);
        $previousCensus = $censusCounts->first();
        $lastYearCensus = $censusCounts->reverse();

        $divisionCensuses = Division::with('census')->get();
        $rankDemographic = $this->clan->allRankDemographic();

        $recruitCount = $this->clan->rankDemographic(1);
        $ncoCount = $this->clan->rankDemographic(range(7, 14));

        return view('statistics.show')->with(compact(
            'memberCount', 'ncoCount', 'previousCensus',
            'lastYearCensus', 'recruitCount', 'memberCount',
            'divisionCensuses', 'rankDemographic'
        ));
    }
}
