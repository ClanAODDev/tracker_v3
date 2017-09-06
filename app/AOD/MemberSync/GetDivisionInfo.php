<?php

namespace App\AOD\MemberSync;

use Log;

/**
 * Handles member data sync from AOD forums
 *
 * @package App\AOD
 */
class GetDivisionInfo
{
    public $data;
    public $division;

    /**
     * AOD member data endpoint
     *
     * @var string
     */
    protected $source = "https://www.clanaod.net/forums/aodinfo.php?";


    /**
     * GetDivisionInfo constructor.
     *
     * @param $division
     */
    public function __construct()
    {
        if (! config('app.aod.token')) {
            Log::critical("ERROR: AOD Token not defined in configuration.");
            exit;
        } else {
            $this->data = $this->fetchData();
        }
    }

    /**
     * Fetches member data per division
     *
     * @return mixed
     */
    protected function fetchData()
    {
        $agent = "AOD Division Tracker";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $this->jsonUrl());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $results = curl_exec($ch);
        $data = json_decode(utf8_encode($results));

        curl_close($ch);

        if (! is_object($data)) {
            Log::critical("ERROR: Member sync returning invalid: {$this->jsonUrl()}");
            Log::critical($results);
            exit;
        }

        if (property_exists($data, 'error')) {
            Log::critical("ERROR: Member sync returned error: {$data->error}");
            exit;
        }

        return $this->prepareData($data);
    }

    /**
     * @return string
     */
    protected function jsonUrl()
    {
        $token = $this->generateToken();

        $arguments = [
            'type' => 'json',
            'authcode2' => $token,
            'extra' => '1'
        ];

        return $this->source . http_build_query($arguments);
    }

    /**
     * Generates authentication token for AOD API
     *
     * @return string
     */
    protected function generateToken()
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . config('app.aod.token'));
    }

    /**
     * forum data dump comes as flat array
     * so we need to build an associative
     * array from the column sort data
     *
     * @param $json
     * @return array
     */
    protected function prepareData($json)
    {
        $prepared = [];
        $memberCount = 0;

        foreach ($json->data as $member) {
            $columnCount = 0;

            foreach ($json->column_order as $column) {
                $prepared[$memberCount][$column] = $member[$columnCount];
                $columnCount++;
            }

            $memberCount++;
        }

        return $prepared;
    }
}
