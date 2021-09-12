<?php

namespace App\Jobs;

use App\AOD\MemberSync\SyncMemberData as MemberSync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMemberData extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle()
    {
        MemberSync::execute();
    }
}
