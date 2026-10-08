<?php

namespace Tests\Unit\Authorization;

use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class RoleChecksAreAbilitiesTest extends TestCase
{
    private const ALLOWED_ROLE_CHECKS = [
        'Http/Middleware/IsBanned.php'                                                         => 1,
        'Support/TicketSerializer.php'                                                         => 1,
        'Http/Controllers/API/TicketApiController.php'                                         => 1,
        'Filament/Admin/Resources/TicketResource/RelationManagers/CommentsRelationManager.php' => 1,
        'Services/TicketNotificationService.php'                                               => 1,
        'Filament/Mod/Resources/MemberAwardResource.php'                                       => 6,
        'Data/PendingActionsData.php'                                                          => 2,
    ];

    #[Test]
    public function permissions_are_checked_through_abilities_not_roles(): void
    {
        $found = [];

        foreach ($this->appFiles() as $relative => $source) {
            $count = preg_match_all('/->isRole\(/', $source) + preg_match_all('/in_array\(\$\w+->role\b/', $source);

            if ($count > 0) {
                $found[$relative] = $count;
            }
        }

        ksort($found);
        $allowed = self::ALLOWED_ROLE_CHECKS;
        ksort($allowed);

        $this->assertSame(
            $allowed,
            $found,
            'Inline role checks changed. Permissions belong in App\Enums\Ability and CodeAbilityMap; only identity checks and role lanes may check a role directly.',
        );
    }

    private function appFiles(): array
    {
        $root  = app_path() . '/';
        $files = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->getExtension() === 'php') {
                $files[substr($file->getPathname(), strlen($root))] = file_get_contents($file->getPathname());
            }
        }

        return $files;
    }
}
