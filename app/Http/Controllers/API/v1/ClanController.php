<?php

namespace App\Http\Controllers\API\v1;

use App\Services\AOD;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function teamspeakPopulationCount()
    {
        $data = AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_ts_population&');

        return $this->respond([
            'data' => strip_tags(preg_replace('/\r|\n/', '', $data))
        ]);
    }
}
