<?php

namespace App\Console\Commands;

use App\Models\Census;
use App\Models\Division;
use Exception;
use Illuminate\Support\Facades\Log;

class DivisionCensus extends BaseCommand
{
    protected $signature = 'tracker:census
                            {--force : Run census even if already performed today}';

    protected $description = 'Collect division census data across all active divisions';

    protected array $stats = [
        'recorded' => 0,
        'errors'   => 0,
    ];

    public function handle(): int
    {
        if (! $this->option('force') && $this->censusAlreadyPerformed()) {
            $this->warn('Census already performed today. Use --force to run anyway.');

            return self::SUCCESS;
        }

        $divisions = Division::active()->get();

        if ($divisions->isEmpty()) {
            $this->info('No active divisions found.');

            return self::SUCCESS;
        }

        $this->info("Beginning division census for {$divisions->count()} divisions...");

        foreach ($divisions as $division) {
            $this->recordEntry($division);
        }

        $this->logStats();

        return self::SUCCESS;
    }

    protected function recordEntry(Division $division): void
    {
        try {
            Census::create([
                'division_id'         => $division->id,
                'count'               => $division->members()->count(),
                'weekly_active_count' => $division->membersActiveSinceDaysAgo(8)->count(),
                'weekly_ts_count'     => $division->membersActiveOnTsSinceDaysAgo(8)->count(),
                'weekly_voice_count'  => $division->membersActiveOnDiscordSinceDaysAgo(8)->count(),
            ]);

            $this->stats['recorded']++;

            if ($this->getOutput()->isVerbose()) {
                $this->line("  Recorded: {$division->name}");
            }
        } catch (Exception $exception) {
            $this->stats['errors']++;
            $this->logError("Failed to record census for {$division->name}", $exception);
        }
    }

    protected function censusAlreadyPerformed(): bool
    {
        return Census::whereDate('created_at', today())->exists();
    }

    protected function logStats(): void
    {
        $message = "Census complete. Recorded: {$this->stats['recorded']}";

        if ($this->stats['errors'] > 0) {
            $message .= ", Errors: {$this->stats['errors']}";
            $this->warn($message);
        } else {
            $this->info($message);
        }

        Log::info('Division census completed', $this->stats);
    }
}
