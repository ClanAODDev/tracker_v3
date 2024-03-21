<?php

namespace App\Models\Slack\Commands;

use App\Jobs\SyncMemberData;
use App\Slack\Base;
use App\Slack\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberSync extends Base implements Command
{
    use DispatchesJobs;

    protected $data;

    /**
     * MemberSync constructor.
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->data = $data;
    }

    /**
     * Handle performing our member sync.
     */
    public function handle()
    {
        $job = new SyncMemberData($this->data);

        $this->dispatch($job);

        return [
            'text' => 'Member sync request has been queued.',
        ];
    }
}
