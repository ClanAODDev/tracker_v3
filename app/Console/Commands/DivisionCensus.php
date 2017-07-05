<?php

namespace App\Console\Commands;

use App\Census;
use App\Division;
use Illuminate\Console\Command;

class DivisionCensus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:divisioncensus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect division census data across all active divisions';

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
        $divisions = Division::active()->get();

        $this->comment('Beginning division census...');

        foreach ($divisions as $division) {
            $this->comment("Recording data for {$division->name}...");

            $this->recordEntry($division);
        }

        $this->comment('Census complete.');
    }

    /**
     * @param $division
     */
    protected function recordEntry(Division $division)
    {
        $census = new Census();
        $census->division()->associate($division);
        $census->count = $division->members->count();
        $census->weekly_active_count = $division->membersActiveSinceDaysAgo(8)->count();
        $census->save();
    }
}
