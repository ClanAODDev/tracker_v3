<?php

namespace Tests\Unit\Authorization;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class UnitLeadershipChecksAreCentralizedTest extends TestCase
{
    private const PATTERN = '/isPlatoonLeader\(|isSquadLeader\(|leader_id\s*(===|==|!==|!=)|(===|==|!==|!=)\s*\$[a-zA-Z_>()?-]*leader_id|Position::(PLATOON_LEADER|SQUAD_LEADER)/';

    private const ALLOWED = [
        'Authorization/PlatoonSquadHierarchy.php'                      => 4,
        'Authorization/UnitTreeHierarchy.php'                          => 4,
        'Console/Commands/UnitsPreflight.php'                          => 6,
        'Filament/Mod/Resources/PlatoonResource/Pages/EditPlatoon.php' => 2,
        'Filament/Mod/Resources/SquadResource/Pages/EditSquad.php'     => 2,
        'Jobs/CleanupUnassignedLeaders.php'                            => 2,
        'Models/Member.php'                                            => 4,
        'Models/Platoon.php'                                           => 1,
        'Transformers/OrgChartTransformer.php'                         => 1,
    ];

    #[Test]
    public function unit_leadership_is_only_checked_through_the_hierarchy(): void
    {
        $root  = app_path() . '/';
        $found = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $count = preg_match_all(self::PATTERN, file_get_contents($file->getPathname()));

            if ($count > 0) {
                $found[substr($file->getPathname(), strlen($root))] = $count;
            }
        }

        ksort($found);
        $allowed = self::ALLOWED;
        ksort($allowed);

        $this->assertSame(
            $allowed,
            $found,
            'Direct platoon/squad leadership checks changed. Authorization belongs in App\Authorization\UnitHierarchy; only leadership assignment and display code may read positions or leader_id directly.',
        );
    }
}
