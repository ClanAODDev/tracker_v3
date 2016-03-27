<?php

namespace App\AOD;

/**
 * Handles member data sync from AOD forums
 *
 * @package App\AOD
 */
class DivisionInfo
{
    public $division;
    public $data;
    protected $source = "http://www.clanaod.net/forums/aodinfo.php?";

    public function __construct($division)
    {
        $this->division = $division;

        if (!getenv('AOD_TOKEN')) {

            $this->data = [
                'error' => 'AOD token not defined in environment'
            ];
            
        } else {
            $this->data = $this->fetchData();
        }
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

        if (array_has($data, 'error')) {
            return $data;
        }

        return $this->prepareData($data);
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
     * Generates authentication token for AOD API
     * @return string
     */
    protected function generateToken()
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . env('AOD_TOKEN'));
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
