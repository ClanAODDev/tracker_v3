<?php

namespace App\Slack\Commands;

use App\AOD\SyncMemberData;
use GuzzleHttp\Client;

class MemberSync implements Command
{
    private $data;

    /**
     * MemberSync constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        SyncMemberData::execute();

        return $this->response();
    }

    public function response()
    {
        $client = new Client;

        $client->post($this->data['response_url'], [
           'json' => [
               'text' => 'Member sync performed successfully'
           ]
        ]);
    }
}
