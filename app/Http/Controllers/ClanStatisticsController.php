<?php

namespace App\Http\Controllers;

use App\Repositories\ClanRepository;
use Illuminate\Http\Request;

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
        $previousCensus = $this->clan->censusCounts(1);
        $lastYearCensus = $this->clan->censusCounts(30)->reverse();
        $recruitCount = $this->clan->rankDemographic(1);
        $ncoCount = $this->clan->rankDemographic(range(7, 14));

        return view('statistics.show')->with(compact(
            'memberCount', 'ncoCount', 'previousCensus', 'lastYearCensus',
            'recruitCount', 'memberCount'));
    }
}
