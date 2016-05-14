<?php

namespace App\Slack\Commands;

use App\AOD\MemberSync\SyncMemberData;
use App\Slack\Base;
use App\Slack\Command;

class MemberSync extends Base implements Command
{
    protected $data;

    /**
     * MemberSync constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Handle performing our member sync
     */
    public function handle()
    {
        SyncMemberData::execute();

        return $this->response();
    }

    /**
     * Provide a response to Slack about the action
     */
    public function response()
    {
        return $this->delayedResponse(
            'Member sync successful'
        );
    }
}
