<?php

namespace App\Models\Slack\Commands;

use App\Models\Division;
use App\Models\Slack\Base;
use App\Models\Slack\Command;

class OutstandingMemberReport extends Base implements Command
{
    /**
     * Handle performing our member sync.
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
                $inactiveCount,
            ];
        }

        return [
            'text' => print_r($data, true),
        ];
    }
}
