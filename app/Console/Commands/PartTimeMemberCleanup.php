<?php

namespace App\Console\Commands;

use App\Models\Division;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PartTimeMemberCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:part-time-member-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove part-time division assignments for members who are already full-time in the same division';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of part-time division assignments...');

        $divisions = Division::with('partTimeMembers')->get();

        foreach ($divisions as $division) {
            foreach ($division->partTimeMembers as $member) {
                if ($member->division_id === $division->name) {
                    $this->info("Cleaning up part-time assignment for member {$member->name} in division {$division->name}");
                    $member->partTimeDivisions()->detach($division->name);
                }
            }
        }

        $this->info('Cleanup completed successfully.');
        return self::SUCCESS;
    }
}
