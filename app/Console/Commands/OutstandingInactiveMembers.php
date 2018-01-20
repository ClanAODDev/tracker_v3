<?php

namespace App\Console\Commands;

use App\Division;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OutstandingInactiveMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:outstanding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provides count of divisional outstanding inactives';

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
        $divisions = Division::active()->orderBy('name')->get();

        $headers = ['Division', 'Outstanding Members', 'Total Inactive'];
        $clanMax = config('app.aod.maximum_days_inactive');

        $data = [];

        foreach ($divisions as $division) {
            $divisionMax = $division->settings()->get('inactivity_days');

            $outstandingCount = $division->members()
                ->whereDoesntHave('leave')
                ->where('last_activity', '<', Carbon::now()->subDays($clanMax)->format('Y-m-d'))
                ->count();

            $inactiveCount = $division->members()
                ->whereDoesntHave('leave')
                ->where('last_activity', '<', Carbon::now()->subDays($divisionMax)->format('Y-m-d'))
                ->count();

            $data[] = [$division->name, $outstandingCount, $inactiveCount-$outstandingCount];
        }

        $this->table($headers, $data);
    }
}
