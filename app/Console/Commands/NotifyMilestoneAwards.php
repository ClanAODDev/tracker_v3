<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Notifications\Channel\NotifyMilestoneAwardReminder;
use App\Repositories\DivisionRepository;
use Exception;
use Illuminate\Support\Collection;

class NotifyMilestoneAwards extends BaseCommand
{
    protected $signature = 'tracker:notify-milestone-awards
                            {--division= : Slug of a specific division to check}
                            {--dry-run : List members with missing awards without sending notifications}';

    protected $description = 'Notify division officer channels of members with ungranted milestone tenure awards';

    protected array $stats = [
        'divisions_notified' => 0,
        'members_flagged'    => 0,
        'errors'             => 0,
    ];

    public function handle(DivisionRepository $repository): int
    {
        $divisions = $this->resolveDivisions();

        if ($divisions->isEmpty()) {
            $this->info('No active divisions found.');

            return self::SUCCESS;
        }

        $monthLabel = now()->format('F Y');
        $dryRun     = (bool) $this->option('dry-run');

        foreach ($divisions as $division) {
            $this->processDivision($division, $repository, $monthLabel, $dryRun);
        }

        $this->logStats($dryRun);

        return self::SUCCESS;
    }

    private function resolveDivisions(): Collection
    {
        if ($slug = $this->option('division')) {
            $division = Division::active()->where('slug', $slug)->first();

            if (! $division) {
                $this->logWarning("No active division found with slug [{$slug}].");

                return collect();
            }

            return collect([$division]);
        }

        return Division::active()->get();
    }

    private function processDivision(Division $division, DivisionRepository $repository, string $monthLabel, bool $dryRun): void
    {
        try {
            $missing = $repository->getDivisionAnniversaries($division)
                ->filter(fn ($member) => $member->has_tenure_award === false);

            if ($missing->isEmpty()) {
                return;
            }

            $this->stats['members_flagged'] += $missing->count();

            if ($dryRun) {
                $this->outputDryRunResults($division->name, $missing);

                return;
            }

            $division->notify(new NotifyMilestoneAwardReminder($missing, $monthLabel));
            $this->stats['divisions_notified']++;
        } catch (Exception $exception) {
            $this->stats['errors']++;
            $this->logError("Failed to process {$division->name}", $exception);
        }
    }

    private function outputDryRunResults(string $divisionName, Collection $missing): void
    {
        $this->line("[{$divisionName}] {$missing->count()} member(s) missing tenure award:");

        $missing->each(
            fn ($m) => $this->line("  - {$m->name} ({$m->years_since_joined} Years of Service)")
        );
    }

    private function logStats(bool $dryRun): void
    {
        $prefix = $dryRun ? '[Dry Run] ' : '';
        $this->info("{$prefix}Milestone check complete.");
        $this->line("  Members flagged: {$this->stats['members_flagged']}");

        if (! $dryRun) {
            $this->line("  Divisions notified: {$this->stats['divisions_notified']}");
        }

        if ($this->stats['errors'] > 0) {
            $this->warn("  Errors: {$this->stats['errors']}");
        }
    }
}
