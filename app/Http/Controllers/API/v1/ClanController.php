<?php

namespace App\Http\Controllers\API\v1;

use App\Services\AOD;
use Illuminate\Http\JsonResponse;

class ClanController extends ApiController
{

    /**
     * ClanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return JsonResponse
     */
    public function teamspeakPopulationCount()
    {
        $data = AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_ts_population_json&');

        return $this->respond([
            'data' => $data
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function discordPopulationCount()
    {
        $data = AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_discord_population_json&');

        return $this->respond([
            'data' => $data
        ]);
    }
}
