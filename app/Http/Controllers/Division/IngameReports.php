<?php

namespace App\Http\Controllers\Division;

use GuzzleHttp\Client;

/**
 * Trait IngameReports.
 */
trait IngameReports
{
    /**
     * Destiny 2 Ingame clan information.
     *
     * @param null|mixed $clanId
     *
     * @return array
     */
    public function destiny2($clanId = null)
    {
        $clans = explode(',', config('app.aod.ingame-reports.destiny-2-clans'));

        $requestedClan = $clanId ?: $clans[0];

        // invalid clan id
        if (!\in_array($requestedClan, $clans, true)) {
            return [];
        }

        // no config for destiny clans
        if (empty($clans[0])) {
            return [];
        }

        return $this->fetchDestiny2ClanData($requestedClan, new Client());
    }

    /**
     * @param $clan
     *
     * @return array
     */
    protected function fetchDestiny2ClanData($clan, Client $client)
    {
        $clanUrl = "https://bungie.net/Platform/GroupV2/{$clan}";
        $memberUrl = "{$clanUrl}/Members/?currentPage=1";

        $clanInformation = $this->getBungieInfo($clanUrl, $client);
        $memberData = $this->getBungieInfo($memberUrl, $client);

        return [
            'clan-info'    => $clanInformation,
            'clan-members' => collect($memberData->results)->sortBy('destinyUserInfo.displayName'),
        ];
    }

    /**
     * @param $url
     *
     * @return mixed
     */
    protected function getBungieInfo($url, Client $client)
    {
        return json_decode($client->request('GET', $url, [
            'headers' => ['X-API-Key' => config('core.bungie.api_key')],
        ])->getBody()->getContents())->Response;
    }
}
