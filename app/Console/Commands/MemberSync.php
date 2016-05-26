<?php

namespace App\Console\Commands;


use App\Jobs\SyncMemberData;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class MemberSync extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'memberSync';

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
        // AOD member data sync
        $job = new SyncMemberData();

        $this->dispatch($job);

        $this->comment('Member sync has been queued.');
    }
}
