<?php

namespace App\AOD\MemberSync;

use App\Services\AODForumService;
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
        if (! config('aod.token')) {
            throw new \Exception('ERROR: AOD Token not defined in configuration.');
            exit;
        }

        $this->data = $this->fetchData();
    }

    /**
     * Fetches member data per division.
     */
    protected function fetchData(): array
    {
        $data = AODForumService::fetchInfo([
            'extra' => true,
            'epoch' => true,
            'type' => 'json',
        ]);

        if (array_key_exists('error', $data)) {
            Log::critical("ERROR: Member sync returned error: {$data['error']}");

            exit;
        }

        return $this->prepareData($data);
    }

    /**
     * forum data dump comes as flat array
     * so we need to build an associative
     * array from the column sort data.
     */
    protected function prepareData($data): array
    {
        $prepared = [];
        $memberCount = 0;

        foreach ($data['data'] as $member) {
            $columnCount = 0;

            foreach ($data['column_order'] as $column) {
                $prepared[$memberCount][$column] = $member[$columnCount];
                $columnCount++;
            }

            $memberCount++;
        }

        return $prepared;
    }
}
