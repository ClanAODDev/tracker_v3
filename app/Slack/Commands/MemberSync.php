<?php

namespace app\Slack\Commands;

use App\AOD\SyncMemberData;

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
        return [
            'text' => 'Member sync performed successfully!'
        ];
    }


}
