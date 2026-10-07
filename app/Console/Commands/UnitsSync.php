<?php

namespace App\Console\Commands;

use App\Services\Units\LegacyUnitSync;

class UnitsSync extends BaseCommand
{
    protected $signature = 'tracker:units-sync';

    protected $description = 'Rebuild the units tree and member unit assignments from platoons and squads (safe to re-run)';

    public function handle(LegacyUnitSync $sync): int
    {
        $result = $sync->sync();

        $this->table(['Platoons', 'Squads', 'Units removed', 'Members assigned', 'Levels created'], [[
            $result['platoons'], $result['squads'], $result['removed'], $result['members'], $result['levels'],
        ]]);

        return self::SUCCESS;
    }
}
