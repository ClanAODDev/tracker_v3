<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeAODToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:aodtoken';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a token for interfacing with AOD API. Valid for one minute.';

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
        $this->comment("curl http://clanaod.net/forums/aodinfo.php?division=battlefront&type=json&authcode=" . $this->generateToken());
    }

    protected function generateToken()
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . config('AOD_TOKEN'));
    }
}
