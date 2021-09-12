<?php

namespace App\Http\Controllers;

use App\Services\AOD;

class WebsiteController extends Controller
{
    public function index()
    {
        $channel = (new ClanAOD\Twitch('clanaodstream'))->getChannel();
        $status = null === $channel->stream ? 'offline' : 'online';

        ddd($channel);

        $commo = [
            'ts'      => AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_ts_population_json&'),
            'discord' => AOD::request('https://www.clanaod.net/forums/aodinfo.php?type=last_discord_population_json&'),
        ];

        return view('website.index', compact('commo'));
    }
}
