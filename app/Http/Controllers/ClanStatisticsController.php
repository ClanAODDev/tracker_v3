<?php

namespace App\Http\Controllers;

use App\Repositories\ClanRepository;

class ClanStatisticsController extends Controller
{
    private $clan;

    public function __construct(ClanRepository $clanRepository)
    {
    }
}
