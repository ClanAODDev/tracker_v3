<?php

namespace App\Console\Commands;

use App\Services\MemberSyncService;

class MemberSync extends BaseCommand
{
    protected $signature = 'tracker:member-sync';

    protected $description = 'Performs member sync with AOD forums';

    public function handle(MemberSyncService $syncService): int
    {
        $this->verbose('Starting member sync...');

        $syncService
            ->onUpdate(fn ($name, $fields) => $this->verbose(
                sprintf('  Updated: %s (%s)', $name, implode(', ', $fields))
            ))
            ->onAdd(fn ($name, $id) => $this->verbose(
                sprintf('  Added: %s (%s)', $name, $id)
            ))
            ->onRemove(fn ($name, $id) => $this->verbose(
                sprintf('  Removed: %s (%s)', $name, $id)
            ));

        if (! $syncService->sync()) {
            $error = $syncService->getLastError() ?? 'No data available from forum';

            return $this->failWithError("Member sync failed - {$error}");
        }

        $this->displayStats($syncService->getStats());

        return self::SUCCESS;
    }

    protected function verbose(string $message): void
    {
        if ($this->getOutput()->isVerbose()) {
            $this->line($message);
        }
    }

    protected function displayStats(array $stats): void
    {
        $message = sprintf(
            'Sync complete. Added: %d, Updated: %d, Removed: %d',
            $stats['added'],
            $stats['updated'],
            $stats['removed']
        );

        if ($stats['errors'] > 0) {
            $message .= sprintf(', Errors: %d', $stats['errors']);
            $this->warn($message);
        } else {
            $this->info($message);
        }
    }
}
