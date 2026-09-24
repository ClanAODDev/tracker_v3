<?php

namespace App\Console\Commands;

use App\Models\Division;
use App\Models\Leave;
use App\Notifications\Channel\NotifyDivisionLoaExpired;
use App\Notifications\Channel\NotifyDivisionLoaExpiring;
use Exception;
use Illuminate\Support\Collection;

class NotifyExpiringLeaves extends BaseCommand
{
    private const EXPIRING_LEAD_DAYS = 3;

    protected $signature = 'tracker:notify-expiring-loas
                            {--division= : Slug of a specific division to check}
                            {--dry-run : List affected leaves without sending notifications}';

    protected $description = 'Notify division channels of leaves of absence expiring soon or just expired';

    protected array $stats = [
        'divisions_notified' => 0,
        'expiring_flagged'   => 0,
        'expired_flagged'    => 0,
        'errors'             => 0,
    ];

    public function handle(): int
    {
        $divisions = $this->resolveDivisions();

        if ($divisions->isEmpty()) {
            $this->info('No active divisions found.');

            return self::SUCCESS;
        }

        $dryRun       = (bool) $this->option('dry-run');
        $expiringDate = today()->addDays(self::EXPIRING_LEAD_DAYS)->toDateString();
        $expiredDate  = today()->subDay()->toDateString();

        foreach ($divisions as $division) {
            $this->processDivision($division, $expiringDate, $expiredDate, $dryRun);
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

    private function processDivision(Division $division, string $expiringDate, string $expiredDate, bool $dryRun): void
    {
        try {
            $expiring = $this->leavesEnding($division, $expiringDate);
            $expired  = $this->leavesEnding($division, $expiredDate);

            if ($expiring->isEmpty() && $expired->isEmpty()) {
                return;
            }

            if ($dryRun) {
                $this->outputDryRunResults($division->name, $expiring, $expired);

                return;
            }

            if ($expiring->isNotEmpty()) {
                $this->stats['expiring_flagged'] += $expiring->count();
                $division->notify(new NotifyDivisionLoaExpiring($expiring));
            }

            if ($expired->isNotEmpty()) {
                $this->stats['expired_flagged'] += $expired->count();
                $division->notify(new NotifyDivisionLoaExpired($expired));
            }

            $this->stats['divisions_notified']++;
        } catch (Exception $exception) {
            $this->stats['errors']++;
            $this->logError("Failed to process {$division->name}", $exception);
        }
    }

    /**
     * @return Collection<int, array{name: string, reason: string, end_date: string}>
     */
    private function leavesEnding(Division $division, string $date): Collection
    {
        return Leave::whereNotNull('approver_id')
            ->whereDate('end_date', $date)
            ->whereHas('member', fn ($query) => $query->where('division_id', $division->id))
            ->with('member')
            ->get()
            ->map(fn (Leave $leave) => [
                'name'     => $leave->member->name,
                'reason'   => Leave::$reasons[$leave->reason] ?? $leave->reason,
                'end_date' => $leave->end_date->format('M j, Y'),
            ]);
    }

    private function outputDryRunResults(string $divisionName, Collection $expiring, Collection $expired): void
    {
        $this->line("[{$divisionName}]");

        if ($expiring->isNotEmpty()) {
            $this->line('  Expiring in ' . self::EXPIRING_LEAD_DAYS . ' days:');
            $expiring->each(fn (array $l) => $this->line("    - {$l['name']} ({$l['reason']}, returns {$l['end_date']})"));
        }

        if ($expired->isNotEmpty()) {
            $this->line('  Expired yesterday:');
            $expired->each(fn (array $l) => $this->line("    - {$l['name']} ({$l['reason']}, expired {$l['end_date']})"));
        }
    }

    private function logStats(bool $dryRun): void
    {
        $prefix = $dryRun ? '[Dry Run] ' : '';
        $this->info("{$prefix}Leave of absence check complete.");
        $this->line("  Expiring flagged: {$this->stats['expiring_flagged']}");
        $this->line("  Expired flagged: {$this->stats['expired_flagged']}");

        if (! $dryRun) {
            $this->line("  Divisions notified: {$this->stats['divisions_notified']}");
        }

        if ($this->stats['errors'] > 0) {
            $this->warn("  Errors: {$this->stats['errors']}");
        }
    }
}
