<?php

namespace App\Console\Commands;

use App\Services\MemberSyncService;

class MemberSync extends BaseCommand
{
    protected $signature = 'tracker:member-sync';

    protected $description = 'Performs member sync with AOD forums';

    public function handle(MemberSyncService $syncService): int
    {
        $syncService
            ->onUpdate(fn ($name, $fields) => $this->info(
                sprintf('Found updates for %s (%s)', $name, implode(',', $fields))
            ))
            ->onAdd(fn ($name, $id) => $this->info(
                sprintf('Added %s - %s', $name, $id)
            ));

        if (! $syncService->sync()) {
            return $this->failWithError('Member sync failed - no data available from forum');
        }

        return self::SUCCESS;
    }
}
