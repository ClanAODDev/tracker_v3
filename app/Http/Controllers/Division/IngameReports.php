<?php

namespace App\Http\Controllers\Division;

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
        $clans = [
            2726986,
            2054476,
            2871880
        ];

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
        $clanData = [];

        foreach ($clans as $clan) {
            $clanUrl = "https://bungie.net/Platform/GroupV2/{$clan}";
            $memberUrl = "{$clanUrl}/Members/?currentPage=1";

            $clanInformation = $this->getBungieInfo($clanUrl, $client);
            $memberData = $this->getBungieInfo($memberUrl, $client);

            $clanData[] = [
                'clan-info' => $clanInformation,
                'clan-members' => $memberData->results
            ];
        }

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