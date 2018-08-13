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
    }
}
