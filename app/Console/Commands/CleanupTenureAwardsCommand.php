<?php

namespace App\Console\Commands;

use App\Jobs\CleanupTenureAwards;
use App\Models\Member;
use App\Services\TenureAwardService;

class CleanupTenureAwardsCommand extends BaseCommand
{
    protected $signature = 'tracker:cleanup-tenure-awards
                            {--silent : Grant missing awards without sending any notifications}
                            {--dry-run : Report the awards that would be granted without granting them}';

    protected $description = 'Grant tenure awards that members have reached but not received';

    public function handle(TenureAwardService $tenureAwards): int
    {
        if ($this->option('dry-run')) {
            $stats = $tenureAwards->reconcile(Member::query()->whereHas('division'), persist: false, removeInvalid: false);

            $this->info("[Dry Run] {$stats['missing']} award(s) would be granted.");

            return self::SUCCESS;
        }

        CleanupTenureAwards::dispatchSync(silent: (bool) $this->option('silent'));

        return $this->succeedWithMessage($this->option('silent') ? 'Tenure awards granted silently.' : 'Tenure awards granted.');
    }
}
