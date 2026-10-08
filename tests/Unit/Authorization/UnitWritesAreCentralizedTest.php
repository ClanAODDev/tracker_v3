<?php

namespace Tests\Unit\Authorization;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class UnitWritesAreCentralizedTest extends TestCase
{
    private const PATTERN = "/'(platoon_id|squad_id|leader_id|unit_id)'\\s*=>|->(platoon_id|squad_id|leader_id|unit_id)\\s*=[^=>]|->(platoon|squad|leader|unit)\\(\\)->(associate|dissociate)|\\b(Platoon|Squad|Unit)::(?!class\\b)[^;]*?->(update|delete|create|restore|forceDelete|insert)\\(/";

    private const ALLOWED = [
        'Filament/Admin/Resources/DivisionResource/Pages/EditDivision.php'                => 3,
        'Filament/Mod/Resources/MemberResource.php'                                       => 1,
        'Filament/Mod/Resources/MemberResource/Pages/EditMember.php'                      => 1,
        'Filament/Mod/Resources/UnitResource/Pages/EditUnit.php'                          => 4,
        'Filament/Mod/Resources/UnitResource/RelationManagers/MembersRelationManager.php' => 1,
        'Http/Controllers/BulkMoveController.php'                                         => 3,
        'Http/Controllers/DivisionController.php'                                         => 1,
        'Http/Controllers/MemberController.php'                                           => 2,
        'Http/Controllers/SquadController.php'                                            => 2,
        'Http/Requests/Squad/AssignSquadMemberRequest.php'                                => 1,
        'Jobs/ResetOrphanedUnitAssignments.php'                                           => 2,
        'Models/Member.php'                                                               => 2,
        'Services/RecruitmentService.php'                                                 => 1,
        'Services/Units/UnitAssignment.php'                                               => 2,
    ];

    #[Test]
    public function unit_data_is_only_written_through_unit_assignment(): void
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
            'Unit data changed outside App\Services\Units. Use UnitAssignment (create, update, setLeader, clearLeadership, archive, restore). BulkMoveController and AssignSquadMemberRequest only validate these keys, and DivisionController only returns one.',
        );
    }
}
