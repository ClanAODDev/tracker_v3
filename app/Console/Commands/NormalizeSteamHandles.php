<?php

namespace App\Console\Commands;

use App\Exceptions\SteamApiException;
use App\Models\Handle;
use App\Models\MemberHandle;
use App\Services\SteamApiService;
use App\Support\Steam\SteamIdParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class NormalizeSteamHandles extends BaseCommand
{
    private const STATUS_FIXED = 'fixed';

    private const STATUS_RESOLVED = 'resolved';

    private const STATUS_SKIPPED = 'skipped';

    private const STATUS_UNFIXABLE = 'unfixable';

    protected $signature = 'tracker:normalize-steam-handles
                            {--dry-run : Report what would change without writing anything}
                            {--include-vanity : Also write SteamIDs resolved from custom URL names}
                            {--skip=* : handle_member IDs to leave untouched}
                            {--delay=1000 : Minimum milliseconds between Steam API calls}
                            {--unfixable : After normalizing, list members whose Steam handle could not be converted, without calling Steam or writing}
                            {--active : With --unfixable, only list members currently in a division}';

    protected $description = 'Convert stored Steam handles to SteamID64 and write a CSV report for review';

    private bool $apiAvailable = true;

    public function handle(SteamApiService $steam): int
    {
        $handle = Handle::where('type', Handle::STEAM_PROFILE)->first();

        if (! $handle) {
            return $this->failWithError('No steam_profile handle type exists.');
        }

        if ($this->option('unfixable')) {
            return $this->listUnfixable($handle);
        }

        if (! config('services.steam.api_key')) {
            return $this->failWithError('Steam API key is not configured (STEAM_API_KEY).');
        }

        $steam->withMinimumInterval((int) $this->option('delay'));

        $rows = $this->loadRows($handle);

        if ($rows->isEmpty()) {
            $this->info('All Steam handles are already SteamID64s.');

            return self::SUCCESS;
        }

        $this->info("Found {$rows->count()} Steam handles to normalize.");

        $rows = $this->resolveVanities($rows, $steam);
        $rows = $this->attachPersonas($rows, $steam);
        $rows = $rows->map(fn (array $row) => $this->decide($row));

        $written = $this->option('dry-run') ? 0 : $this->write($rows);

        $this->summarize($rows, $written);

        return $this->apiAvailable ? self::SUCCESS : self::FAILURE;
    }

    private function loadRows(Handle $handle): Collection
    {
        return MemberHandle::query()
            ->where('handle_id', $handle->id)
            ->with(['member' => fn ($query) => $query->withTrashed()->select('id', 'name', 'clan_id', 'division_id', 'deleted_at')->with('division:id,name')])
            ->orderBy('id')
            ->get()
            ->reject(fn (MemberHandle $memberHandle) => SteamIdParser::isValidId64($memberHandle->value))
            ->map(fn (MemberHandle $memberHandle) => [
                'id'          => $memberHandle->id,
                'member_id'   => $memberHandle->member_id,
                'member_name' => $memberHandle->member?->name,
                'clan_id'     => $memberHandle->member?->clan_id,
                'division'    => $memberHandle->member?->division?->name,
                'active'      => (bool) $memberHandle->member?->division_id && ! $memberHandle->member?->trashed(),
                'original'    => $memberHandle->value,
                'parsed'      => SteamIdParser::parse($memberHandle->value),
                'steam_id'    => null,
                'persona'     => null,
                'status'      => null,
                'note'        => null,
            ])
            ->values();
    }

    private function listUnfixable(Handle $handle): int
    {
        $rows = $this->loadRows($handle)
            ->filter(fn (array $row) => ! $row['parsed']->steamId || $row['parsed']->format->isGuessedFromNumber())
            ->when($this->option('active'), fn (Collection $rows) => $rows->where('active', true))
            ->sortBy([['division', 'asc'], ['member_name', 'asc']]);

        if ($rows->isEmpty()) {
            $this->info('Every Steam handle is a SteamID64.');

            return self::SUCCESS;
        }

        $this->table(
            ['Member', 'Clan ID', 'Division', 'Value', 'Reason'],
            $rows->map(fn (array $row) => [
                $row['member_name'],
                $row['clan_id'],
                $row['division'] ?? '—',
                $row['original'],
                $row['parsed']->reason ?? ($row['parsed']->needsLookup() ? 'No Steam account uses this custom URL' : 'No Steam account has this friend code'),
            ]),
        );

        $this->info("{$rows->count()} members need to re-enter their SteamID64.");

        return self::SUCCESS;
    }

    private function resolveVanities(Collection $rows, SteamApiService $steam): Collection
    {
        $lookups = $rows->filter(fn (array $row) => $row['parsed']->needsLookup());

        if ($lookups->isEmpty()) {
            return $rows->map(fn (array $row) => [...$row, 'steam_id' => $row['parsed']->steamId]);
        }

        $this->info("Resolving {$lookups->count()} custom URL names through the Steam API...");

        $resolved = [];
        $bar      = $this->output->createProgressBar($lookups->count());

        foreach ($lookups as $row) {
            $vanity = $row['parsed']->vanity;

            try {
                $resolved[strtolower($vanity)] ??= $steam->resolveVanity($vanity) ?? false;
            } catch (SteamApiException $e) {
                $this->apiAvailable = false;
                $this->newLine();
                $this->logError("Stopped resolving custom URLs: {$e->getMessage()} Re-run later; finished lookups are cached.");

                break;
            }

            $bar->advance();
        }

        if ($this->apiAvailable) {
            $bar->finish();
            $this->newLine();
        }

        return $rows->map(function (array $row) use ($resolved) {
            if (! $row['parsed']->needsLookup()) {
                return [...$row, 'steam_id' => $row['parsed']->steamId];
            }

            $steamId = $resolved[strtolower($row['parsed']->vanity)] ?? null;

            return [...$row, 'steam_id' => $steamId ?: null, 'note' => match ($steamId) {
                null    => 'Steam API unavailable',
                false   => 'No Steam account uses this custom URL',
                default => null,
            }];
        });
    }

    private function attachPersonas(Collection $rows, SteamApiService $steam): Collection
    {
        $steamIds  = $rows->pluck('steam_id')->filter()->all();
        $summaries = null;

        if ($this->apiAvailable && $steamIds !== []) {
            try {
                $summaries = $steam->playerSummaries($steamIds);
            } catch (SteamApiException $e) {
                $this->apiAvailable = false;
                $this->logError("Could not fetch Steam persona names: {$e->getMessage()}");
            }
        }

        return $rows->map(function (array $row) use ($summaries) {
            if (! $row['steam_id']) {
                return $row;
            }

            if ($summaries === null) {
                return $row['parsed']->format->isGuessedFromNumber()
                    ? [...$row, 'steam_id' => null, 'note' => 'Could not verify the account with the Steam API']
                    : $row;
            }

            $persona = $summaries[$row['steam_id']]['personaName'] ?? null;

            if (! $persona && $row['parsed']->format->isGuessedFromNumber()) {
                return [...$row, 'steam_id' => null, 'note' => 'No Steam account has this friend code'];
            }

            return [...$row, 'persona' => $persona];
        });
    }

    private function decide(array $row): array
    {
        if (in_array((string) $row['id'], $this->option('skip'), true)) {
            return [...$row, 'status' => self::STATUS_SKIPPED];
        }

        if (! $row['steam_id']) {
            return [...$row, 'status' => self::STATUS_UNFIXABLE, 'note' => $row['note'] ?? $row['parsed']->reason];
        }

        if ($row['parsed']->needsLookup()) {
            return [...$row, 'status' => self::STATUS_RESOLVED];
        }

        return [...$row, 'status' => self::STATUS_FIXED];
    }

    private function write(Collection $rows): int
    {
        $writable = [self::STATUS_FIXED, ...($this->option('include-vanity') ? [self::STATUS_RESOLVED] : [])];

        return $rows
            ->filter(fn (array $row) => in_array($row['status'], $writable, true))
            ->each(fn (array $row) => MemberHandle::whereKey($row['id'])->update(['value' => $row['steam_id']]))
            ->count();
    }

    private function summarize(Collection $rows, int $written): void
    {
        $this->table(
            ['Status', 'Format', 'Active members', 'All members'],
            $rows->groupBy(fn (array $row) => "{$row['status']}|{$row['parsed']->format->label()}")
                ->sortKeys()
                ->map(fn (Collection $group, string $key) => [
                    ...explode('|', $key),
                    $group->where('active', true)->count(),
                    $group->count(),
                ])
                ->values(),
        );

        $path = $this->writeReport($rows);

        $this->info($this->option('dry-run') ? 'Dry run: nothing was written.' : "Updated {$written} Steam handles.");

        if (! $this->option('include-vanity') && $rows->contains('status', self::STATUS_RESOLVED)) {
            $this->warn('Resolved custom URLs were not written. Re-run with --include-vanity to write them.');
        }

        $this->info("Report: {$path}");
    }

    private function writeReport(Collection $rows): string
    {
        $csv = fopen('php://temp', 'r+');

        fputcsv($csv, ['handle_member_id', 'member_id', 'member_name', 'active', 'original', 'format', 'steam_id', 'persona_name', 'status', 'note']);

        $rows->sortBy([['status', 'asc'], ['active', 'desc']])->each(fn (array $row) => fputcsv($csv, [
            $row['id'],
            $row['member_id'],
            $row['member_name'],
            $row['active'] ? 'yes' : 'no',
            $row['original'],
            $row['parsed']->format->label(),
            $row['steam_id'],
            $row['persona'],
            $row['status'],
            $row['note'],
        ]));

        rewind($csv);

        $path = 'steam-handles-' . now()->format('Ymd-His') . '.csv';
        Storage::disk('local')->put($path, stream_get_contents($csv));
        fclose($csv);

        return Storage::disk('local')->path($path);
    }
}
