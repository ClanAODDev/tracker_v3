<?php

namespace App\AOD;

/**
 * Handles member data sync from AOD forums
 *
 * @package App\AOD
 */
class DivisionInfo
{
    protected $source = "http://www.clanaod.net/forums/aodinfo.php?";

    public $division;
    public $data;

    public function __construct($division)
    {
        $this->division = $division;
        $this->data = $this->fetchData();
    }

    /**
     * Generates authentication token for AOD API
     * @return string
     */
    protected function generateToken()
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . env('AOD_TOKEN'));
    }

    /**
     * @return string
     * @internal param $division
     * @internal param $token
     */
    protected function jsonUrl()
    {
        $token = $this->generateToken();

        $arguments = [
            'type' => 'json',
            'division' => $this->division,
            'authcode' => $token,
        ];

        return $this->source . http_build_query($arguments);
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

    /**
     * Fetches member data per division
     *
     * @return mixed
     * @internal param $agent
     */
    protected function fetchData()
    {
        $agent = "AOD Division Tracker";
        $json_url = $this->jsonUrl();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $json_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $data = json_decode(
            curl_exec($ch)
        );

        return $this->prepareData($data);
    }

}
