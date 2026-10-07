<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role;
use App\Models\Division;
use App\Models\Unit;
use PHPUnit\Framework\Attributes\Test;

class OrgStructurePolicyMatrixTest extends PermissionMatrixTestCase
{
    private const PLATOONS = ['own_platoon' => 'A1', 'other_platoon' => 'A2', 'other_division_platoon' => 'B1'];

    private const SQUADS = ['own_squad' => 'A1a', 'same_platoon_other_squad' => 'A1b', 'other_platoon_squad' => 'A2a', 'other_division_squad' => 'B1a'];

    #[Test]
    public function division_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('DivisionPolicy', $this->evaluate(PermissionWorld::ACTORS, $this->divisionChecks()));
    }

    #[Test]
    public function platoon_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('PlatoonPolicy', $this->evaluate(PermissionWorld::ACTORS, $this->platoonChecks()));
    }

    #[Test]
    public function platoon_policy_for_unit_leaders_without_the_officer_role_matches_the_recorded_matrix(): void
    {
        $this->buildWorld(Role::MEMBER);

        $this->assertMatchesSnapshot('PlatoonPolicy.unit-leaders-with-member-role', $this->evaluate(['platoon_leader', 'squad_leader'], $this->platoonChecks()));
    }

    #[Test]
    public function squad_policy_matches_the_recorded_matrix(): void
    {
        $this->buildWorld();

        $this->assertMatchesSnapshot('SquadPolicy', $this->evaluate(PermissionWorld::ACTORS, $this->squadChecks()));
    }

    #[Test]
    public function squad_policy_for_unit_leaders_without_the_officer_role_matches_the_recorded_matrix(): void
    {
        $this->buildWorld(Role::MEMBER);

        $this->assertMatchesSnapshot('SquadPolicy.unit-leaders-with-member-role', $this->evaluate(['platoon_leader', 'squad_leader'], $this->squadChecks()));
    }

    private function divisionChecks(): array
    {
        $checks = [];

        foreach (['create', 'viewAny', 'deleteAny', 'show'] as $ability) {
            $checks[] = [$ability, '(any division)', fn () => [Division::class]];
        }

        foreach (['view', 'update', 'delete'] as $ability) {
            $checks[] = [$ability, 'own_division', fn () => [$this->world->divisionA->fresh()]];
            $checks[] = [$ability, 'other_division', fn () => [$this->world->divisionB->fresh()]];
        }

        return $checks;
    }

    private function platoonChecks(): array
    {
        $checks = [
            ['viewAny', '(any platoon)', fn () => [Unit::class]],
            ['create', '(no division)', fn ()  => [Unit::class]],
            ['create', 'own_division', fn ()   => [Unit::class, $this->world->divisionA->fresh()]],
            ['create', 'other_division', fn () => [Unit::class, $this->world->divisionB->fresh()]],
        ];

        foreach (['update', 'delete'] as $ability) {
            foreach (self::PLATOONS as $label => $platoon) {
                $checks[] = [$ability, $label, fn () => [$this->world->platoons[$platoon]->fresh()]];
            }
        }

        return $checks;
    }

    private function squadChecks(): array
    {
        $checks = [];

        foreach (['viewAny', 'deleteAny', 'create'] as $ability) {
            $checks[] = [$ability, '(any squad)', fn () => [Unit::class]];
        }

        foreach (['update', 'delete'] as $ability) {
            foreach (self::SQUADS as $label => $squad) {
                $checks[] = [$ability, $label, fn () => [$this->world->squads[$squad]->fresh()]];
            }
        }

        return $checks;
    }
}
