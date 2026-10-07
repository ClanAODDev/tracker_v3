<?php

namespace Tests\Unit\Authorization;

use App\Authorization\PlatoonSquadHierarchy;
use App\Authorization\UnitTreeHierarchy;
use App\Enums\Position;
use App\Enums\UnitLevel;
use App\Models\Member;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitTreeHierarchyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_answers_exactly_like_the_platoon_and_squad_hierarchy(): void
    {
        $division = $this->createActiveDivision();
        $other    = $this->createActiveDivision();
        $p1       = $this->createPlatoon($division);
        $p2       = $this->createPlatoon($division);
        $s1a      = $this->createSquad($p1);
        $s1b      = $this->createSquad($p1);
        $s2a      = $this->createSquad($p2);
        $archived = $this->createSquad($p1);
        app(UnitAssignment::class)->archive(app(UnitAssignment::class)->forLegacy($archived));
        $pOther = $this->createPlatoon($other);

        $members = collect([
            'pl of p1'         => $this->createPlatoonLeader($p1, ['division_id' => $division->id]),
            'sl of s1a'        => $this->createSquadLeader($s1a, ['division_id' => $division->id]),
            'pl without unit'  => $this->createMember(['division_id' => $division->id, 'position' => Position::PLATOON_LEADER]),
            'sl without squad' => $this->createMember(['division_id' => $division->id, 'platoon_id' => $p2->id, 'position' => Position::SQUAD_LEADER]),
            'unassigned'       => $this->createMember(['division_id' => $division->id]),
            'platoon level p1' => $this->createMember(['division_id' => $division->id, 'platoon_id' => $p1->id]),
            'in s1a'           => $this->createMember(['division_id' => $division->id, 'platoon_id' => $p1->id, 'squad_id' => $s1a->id]),
            'in s1b'           => $this->createMember(['division_id' => $division->id, 'platoon_id' => $p1->id, 'squad_id' => $s1b->id]),
            'in s2a'           => $this->createMember(['division_id' => $division->id, 'platoon_id' => $p2->id, 'squad_id' => $s2a->id]),
            'other division'   => $this->createMember(['division_id' => $other->id, 'platoon_id' => $pOther->id]),
        ])->map->fresh();

        $legacy = new PlatoonSquadHierarchy;
        $tree   = $this->app->make(UnitTreeHierarchy::class);

        foreach ($members as $leaderName => $leader) {
            foreach ([$p1, $p2, $s1a, $s1b, $s2a, $pOther] as $unit) {
                $this->assertSame($legacy->leads($leader, $unit->fresh()), $tree->leads($leader, $unit->fresh()), "leads: {$leaderName} / " . class_basename($unit) . " {$unit->id}");
            }

            foreach (UnitLevel::cases() as $level) {
                $this->assertEqualsCanonicalizing(
                    $legacy->scopeToLedUnit(Member::query(), $leader, $level)->pluck('id')->all(),
                    $tree->scopeToLedUnit(Member::query(), $leader, $level)->pluck('id')->all(),
                    "scopeToLedUnit: {$leaderName} at {$level->name}",
                );
            }

            foreach ($members as $memberName => $member) {
                $this->assertSame($legacy->leadsUnitOf($leader, $member), $tree->leadsUnitOf($leader, $member), "leadsUnitOf: {$leaderName} -> {$memberName}");

                foreach (UnitLevel::cases() as $level) {
                    $this->assertSame(
                        $legacy->sharesLedUnit($leader, $member, $level),
                        $tree->sharesLedUnit($leader, $member, $level),
                        "sharesLedUnit: {$leaderName} -> {$memberName} at {$level->name}",
                    );
                }
            }
        }
    }
}
