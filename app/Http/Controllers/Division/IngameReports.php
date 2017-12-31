<?php

namespace App\Http\Controllers\Division;

use App\Division;
use App\Handle;
use GuzzleHttp\Client;

/**
 * Trait IngameReports
 *
 * @package App\Http\Controllers\Division
 */
trait IngameReports
{
    /**
     * Destiny 2 Ingame clan information
     *
     * @return array
     */
    public function destiny2()
    {
        $clans = explode(',', config('app.aod.ingame-reports.destiny-2-clans'));

        // no config for destiny clans
        if (empty($clans[0])) {
            return [];
        }

        $client = new Client();

        return $this->fetchDestiny2ClanData($clans, $client);
    }

    /**
     * @param array $clans
     * @param Client $client
     * @return array
     */
    protected function fetchDestiny2ClanData(array $clans, Client $client)
    {
        $division = request()->route('division');

        $fullTimers = $division->members()->with([
            'handles' => function ($query) use ($division) {
                $query->whereHandleId($division->handle_id);
            }
        ])->get();

        $clanData = [];

        foreach ($clans as $clan) {
            $clanUrl = "https://bungie.net/Platform/GroupV2/{$clan}";
            $memberUrl = "{$clanUrl}/Members/?currentPage=1";

            $clanInformation = $this->getBungieInfo($clanUrl, $client);
            $memberData = $this->getBungieInfo($memberUrl, $client);

            $bungieIds[] = collect($memberData->results)->pluck('bungieNetUserInfo.membershipId');

            $clanData[] = [
                'clan-info' => $clanInformation,
                'clan-members' => $memberData->results
            ];
        }

//        dd(array_diff(
//            $fullTimers->pluck('divisionHandle', 'name')->toArray(),
//            array_flatten($bungieIds)
//        ));

//        array_diff()


//        dd(array_flatten($bungieIds));

        return $clanData;
    }

    /**
     * @param $url
     * @param Client $client
     * @return mixed
     */
    protected function getBungieInfo($url, Client $client)
    {
        return json_decode($client->request('GET', $url, [
            'headers' => ['X-API-Key' => config('services.bungie.api_key')]
        ])->getBody()->getContents())->Response;
    }
}