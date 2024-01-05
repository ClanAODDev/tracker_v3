<?php

namespace App\Console\Commands;

use App\Models\Division;

class DivisionCensus extends \Illuminate\Console\Command
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
        if ($this->censusAlreadyPerformed()) {
            $this->alert('Census already performed. No action taken.');

            exit;
        }
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
        $census = new \App\Models\Census();
        $census->division()->associate($division);
        $census->count = $division->members->count();
        $census->weekly_active_count = $division->membersActiveSinceDaysAgo(8)->count();
        $census->weekly_ts_count = $division->membersActiveOnTsSinceDaysAgo(8)->count();
        $census->save();
    }

    /**
     * @return bool
     */
    private function censusAlreadyPerformed()
    {
        $census = \App\Models\Census::latest()->first();

        return $census->created_at->format('Y-m-d') === now()->format('Y-m-d');
    }
}
