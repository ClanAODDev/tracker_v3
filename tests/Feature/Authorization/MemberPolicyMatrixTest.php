<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role;
use App\Models\Member;
use PHPUnit\Framework\Attributes\Test;

class MemberPolicyMatrixTest extends PermissionMatrixTestCase
{
    private const CLASS_ABILITIES = ['recruit', 'create', 'flagInactive', 'viewAny', 'remindActivity'];

    private const MEMBER_ABILITIES = [
        'view',
        'update',
        'reset',
        'delete',
        'separate',
        'promote',
        'updateLeave',
        'remindActivity',
        'clearActivityReminders',
        'managePartTime',
        'manageHandles',
        'manageFields',
    ];

    #[Test]
    public function member_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('MemberPolicy', $this->matrix(PermissionWorld::ACTORS));
    }

    #[Test]
    public function member_policy_for_unit_leaders_without_the_officer_role_matches_the_recorded_matrix(): void
    {
        $this->buildWorld(Role::MEMBER);

        $this->assertMatchesSnapshot('MemberPolicy.unit-leaders-with-member-role', $this->matrix(['platoon_leader', 'squad_leader']));
    }

    private function matrix(array $actors): array
    {
        $lines = [];

        foreach (self::CLASS_ABILITIES as $ability) {
            foreach ($actors as $actor) {
                $lines[] = self::line($ability, '(any member)', $actor, $this->gate($actor, $ability, [Member::class]));
            }
        }

        foreach (self::MEMBER_ABILITIES as $ability) {
            foreach (PermissionWorld::TARGETS as $target) {
                foreach ($actors as $actor) {
                    $lines[] = self::line($ability, $target, $actor, $this->memberCheck($actor, $target, $ability));
                }
            }
        }

        foreach ($this->world->fields as $fieldKey => $field) {
            foreach (PermissionWorld::TARGETS as $target) {
                foreach ($actors as $actor) {
                    $lines[] = self::line('manageField', "{$target} / {$fieldKey}", $actor, $this->memberCheck($actor, $target, 'manageField', [$field]));
                }
            }
        }

        return $lines;
    }

    private function memberCheck(string $actor, string $target, string $ability, array $extra = []): string
    {
        $member = $this->world->target($actor, $target);

        if (! $member) {
            return 'n/a';
        }

        return $this->gate($actor, $ability, [$member->fresh(), ...$extra]);
    }
}
