<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\AOD\MemberSync\SyncMemberData;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberSync extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:membersync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs member sync with AOD forums';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        SyncMemberData::execute();

        $this->comment('Member sync has been queued.');
    }
}
