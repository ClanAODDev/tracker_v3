<?php

namespace App\AOD\MemberSync;

use App\Services\AOD;
use Log;

class GetDivisionInfo
{
    public $data;

    public $division;

    /**
     * AOD member data endpoint.
     *
     * @var string
     */
    protected $source = 'https://www.clanaod.net/forums/aodinfo.php?';

    /**
     * GetDivisionInfo constructor.
     */
    public function __construct()
    {
        if (! config('app.aod.token')) {
            throw new \Exception('ERROR: AOD Token not defined in configuration.');
            exit;
        }

        $this->data = $this->fetchData();
    }

    /**
     * Fetches member data per division.
     *
     * @return mixed
     */
    protected function fetchData()
    {
        $data = AOD::request($this->source, [
            'extra' => 1,
            'type' => 'json',
        ]);

        if (! \is_object($data)) {
            Log::critical('ERROR: Member sync returning invalid.');

            exit;
        }

        if (property_exists($data, 'error')) {
            Log::critical("ERROR: Member sync returned error: {$data->error}");

            exit;
        }

        return $this->prepareData($data);
    }

    /**
     * forum data dump comes as flat array
     * so we need to build an associative
     * array from the column sort data.
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
