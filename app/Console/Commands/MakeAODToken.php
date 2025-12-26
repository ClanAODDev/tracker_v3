<?php

namespace App\Console\Commands;

class MakeAODToken extends BaseCommand
{
    protected $signature = 'tracker:make-token';

    protected $description = 'Create a new token for interfacing with AOD API. Valid for one minute.';

    public function handle(): int
    {
        $token = $this->generateToken();

        $this->info('Token generated (valid for 1 minute):');
        $this->newLine();
        $this->line($token);
        $this->newLine();
        $this->comment('Example usage:');
        $this->line('curl "http://clanaod.net/forums/aodinfo.php?division=battlefront&type=json&authcode=' . $token . '"');

        return self::SUCCESS;
    }

    protected function generateToken(): string
    {
        $currentMinute = floor(time() / 60) * 60;

        return md5($currentMinute . config('aod.token'));
    }
}
