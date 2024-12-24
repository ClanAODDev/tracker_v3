<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneTenureAwards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-tenure-awards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and remove extraneous tenure awards, leaving only the highest earned';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $serviceAwardIds = [18, 19, 139, 40];

        $memberAwards = DB::table('award_member')
            ->whereIn('award_id', $serviceAwardIds)
            ->get()
            ->groupBy('member_id');

        foreach ($memberAwards as $memberId => $awards) {

            $highestAward = $awards->pluck('award_id')->max();

            $extraneousAwards = $awards->where('award_id', '<', $highestAward);

            if ($extraneousAwards->count()) {
                $this->info(sprintf('Found %d extraneous tenure awards to cleanup', $extraneousAwards->count()));

                DB::table('award_member')
                    ->where('member_id', $memberId)
                    ->whereIn('award_id', $extraneousAwards->pluck('award_id'))
                    ->delete();
            }
        }

        $this->info('Service awards cleaned up!');
    }
}
