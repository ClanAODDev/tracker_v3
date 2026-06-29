<?php

namespace App\Console\Commands;

use App\Models\Census;
use App\Models\Division;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DivisionCensus extends BaseCommand
{
    protected $signature = 'tracker:census
                            {--force : Run census even if already performed today}';

    protected $description = 'Collect division census data across all active divisions';

    public function handle(): int
    {
        if (! $this->option('force') && $this->censusAlreadyPerformed()) {
            $this->warn('Census already performed today. Use --force to run anyway.');

            return self::SUCCESS;
        }

        $weekAgo   = now()->subDays(8)->toDateString();
        $divisions = Division::active()
            ->withCount([
                'members',
                'members as weekly_active_count' => fn ($q) => $q->where('last_activity', '>=', $weekAgo),
                'members as weekly_voice_count'  => fn ($q) => $q->where('last_voice_activity', '>=', $weekAgo),
            ])
            ->get();

        if ($divisions->isEmpty()) {
            $this->info('No active divisions found.');

            return self::SUCCESS;
        }

        $this->info("Beginning division census for {$divisions->count()} divisions...");

        try {
            $recorded = $this->recordEntries($divisions);
            $this->info("Census complete. Recorded: {$recorded}");
            Log::info('Division census completed', ['recorded' => $recorded]);
        } catch (Exception $exception) {
            $this->logError('Census failed', $exception);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function recordEntries(Collection $divisions): int
    {
        $now  = now();
        $rows = $divisions->map(fn (Division $division) => [
            'division_id'         => $division->id,
            'count'               => $division->members_count,
            'weekly_active_count' => $division->weekly_active_count,
            'weekly_voice_count'  => $division->weekly_voice_count,
            'created_at'          => $now,
            'updated_at'          => $now,
        ])->all();

        Census::insert($rows);

        return count($rows);
    }

    protected function censusAlreadyPerformed(): bool
    {
        return Census::whereDate('created_at', today())->exists();
    }
}
