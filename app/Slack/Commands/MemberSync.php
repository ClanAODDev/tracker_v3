<?php

namespace App\Slack\Commands;

use App\Slack\Base;
use App\Slack\Command;
use App\Jobs\SyncMemberData;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberSync extends Base implements Command
{
    use DispatchesJobs;

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
        $job = new SyncMemberData($this->data);

        $this->dispatch($job);

        return [
            'text' => 'Member sync request has been queued.'
        ];
    }
}
