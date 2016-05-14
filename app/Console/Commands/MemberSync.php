<?php

namespace App\Console\Commands;

use App\AOD\MemberSync\SyncMemberData;
use Illuminate\Console\Command;

class MemberSync extends Command
{
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
        SyncMemberData::execute();
    }
}
