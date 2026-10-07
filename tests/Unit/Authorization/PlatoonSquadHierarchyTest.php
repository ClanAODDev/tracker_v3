<?php

namespace Tests\Unit\Authorization;

use App\Authorization\UnitHierarchy;
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

class PlatoonSquadHierarchyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private UnitHierarchy $units;

    protected function setUp(): void
    {
        parent::setUp();

        $this->units = $this->app->make(UnitHierarchy::class);
    }

    #[Test]
    public function units_back_the_bound_hierarchy(): void
    {
        $this->assertInstanceOf(UnitTreeHierarchy::class, $this->units);
    }

    #[Test]
    public function leadership_level_follows_the_members_position(): void
    {
        $division = $this->createActiveDivision();

        foreach (Position::cases() as $position) {
            $user = $this->createMemberWithUser(['division_id' => $division->id, 'position' => $position]);

            $expected = match ($position) {
                Position::PLATOON_LEADER => UnitLevel::Platoon,
                Position::SQUAD_LEADER   => UnitLevel::Squad,
                default                  => null,
            };

            $this->assertSame($expected, $this->units->leadershipLevel($user), $position->name);
        }

        $this->assertNull($this->units->leadershipLevel($this->createMemberWithUser()->setRelation('member', null)));
    }

    #[Test]
    public function leads_is_true_only_for_the_units_leader(): void
    {
        $squad   = $this->createSquad();
        $platoon = $squad->platoon;
        $leader  = $this->createSquadLeader($squad);
        $other   = $this->createMember(['division_id' => $platoon->division_id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $pl      = $this->createPlatoonLeader($platoon);

        $this->assertTrue($this->units->leads($leader, $squad));
        $this->assertFalse($this->units->leads($other, $squad));
        $this->assertTrue($this->units->leads($pl, $platoon->fresh()));
        $this->assertFalse($this->units->leads($leader, $platoon->fresh()));

        $assignment = app(UnitAssignment::class);
        $assignment->setLeader($assignment->forLegacy($squad), null);
        $this->assertFalse($this->units->leads($leader, $squad->fresh()));
    }

    #[Test]
    public function leads_unit_of_covers_the_members_squad_and_platoon_leaders(): void
    {
        $squad     = $this->createSquad();
        $platoon   = $squad->platoon;
        $sl        = $this->createSquadLeader($squad);
        $pl        = $this->createPlatoonLeader($platoon);
        $member    = $this->createMember(['division_id' => $platoon->division_id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $elsewhere = $this->createMember(['division_id' => $platoon->division_id]);

        $this->assertTrue($this->units->leadsUnitOf($sl, $member));
        $this->assertTrue($this->units->leadsUnitOf($pl, $member));
        $this->assertFalse($this->units->leadsUnitOf($sl, $elsewhere));
        $this->assertFalse($this->units->leadsUnitOf($pl, $elsewhere));
    }

    #[Test]
    public function shares_led_unit_and_scope_compare_the_leaders_own_assignment(): void
    {
        $squad    = $this->createSquad();
        $division = $squad->platoon->division_id;
        $leader   = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $squad->id]);
        $sameSq   = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $squad->id]);
        $otherSq  = $this->createMember(['division_id' => $division, 'platoon_id' => $squad->platoon_id, 'squad_id' => $this->createSquad($squad->platoon)->id]);

        $this->assertTrue($this->units->sharesLedUnit($leader, $sameSq, UnitLevel::Squad));
        $this->assertFalse($this->units->sharesLedUnit($leader, $otherSq, UnitLevel::Squad));
        $this->assertTrue($this->units->sharesLedUnit($leader, $otherSq, UnitLevel::Platoon));

        $inSquad   = $this->units->scopeToLedUnit(Member::query(), $leader, UnitLevel::Squad)->pluck('id');
        $inPlatoon = $this->units->scopeToLedUnit(Member::query(), $leader, UnitLevel::Platoon)->pluck('id');

        $this->assertEqualsCanonicalizing([$leader->id, $sameSq->id], $inSquad->all());
        $this->assertEqualsCanonicalizing([$leader->id, $sameSq->id, $otherSq->id], $inPlatoon->all());
    }
}
