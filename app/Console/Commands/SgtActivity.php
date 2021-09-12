<?php

namespace App\Console\Commands;

use App\Models\Member;
use Carbon;
use Illuminate\Console\Command;

class SgtActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:sgt-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches activity stats for current Sergeants';

    /**
     * Create a new command instance.
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
        $sgts = Member::where('rank_id', '>=', 9)
            ->where('division_id', '!=', 0)
            ->where('division_id', '!=', 7)
            ->select('name', 'last_activity', 'last_ts_activity')
            ->orderBy('last_activity')->get();

        $headers = ['name', 'forum activity', 'ts activity'];

        $this->table($headers, $sgts->map(fn ($sgt) => [
            'name'                => $sgt->name,
            'last_forum_activity' => Carbon::parse($sgt->last_activity)->diffForHumans(),
            'last_ts_activity'    => Carbon::parse($sgt->last_ts_activity)->diffForHumans(),
        ]));
    }
}
