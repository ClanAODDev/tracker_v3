<?php

namespace App\Slack\Commands;

use App\Division;
use App\Slack\Base;
use App\Slack\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OutstandingMemberReport extends Base implements Command
{
    use DispatchesJobs;

    /**
     * Handle performing our member sync
     */
    public function handle()
    {
        $divisions = Division::active()->orderBy('name')->get();

        $headers = ['Division', '> 90', '> Div. Max', 'Total'];
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

            $data[] = [
                $division->name,
                $outstandingCount,
                $inactiveCount - $outstandingCount,
                $inactiveCount
            ];
        }

        return [
            'text' => $data
        ];
    }
}
