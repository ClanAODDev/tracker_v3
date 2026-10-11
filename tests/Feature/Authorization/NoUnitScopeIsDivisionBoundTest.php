<?php

namespace Tests\Feature\Authorization;

use App\Authorization\UnitHierarchy;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\UnitLevel;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NoUnitScopeIsDivisionBoundTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_member_without_a_unit_only_sees_unassigned_members_of_their_own_division(): void
    {
        $home  = $this->createActiveDivision();
        $other = $this->createActiveDivision();

        $user = $this->createMemberWithUser(['division_id' => $home->id, 'unit_id' => null, 'position' => Position::MEMBER, 'rank' => Rank::SERGEANT]);

        $homeUnassigned  = $this->createMember(['division_id' => $home->id, 'unit_id' => null, 'rank' => Rank::RECRUIT]);
        $otherUnassigned = $this->createMember(['division_id' => $other->id, 'unit_id' => null, 'rank' => Rank::RECRUIT]);

        $eligible = Member::eligibleForRankAction($user)->pluck('id');

        $this->assertContains($homeUnassigned->id, $eligible);
        $this->assertNotContains($otherUnassigned->id, $eligible);
    }

    #[Test]
    public function members_without_units_in_different_divisions_do_not_share_a_led_unit(): void
    {
        $home  = $this->createActiveDivision();
        $other = $this->createActiveDivision();

        $leader    = $this->createMember(['division_id' => $home->id, 'unit_id' => null]);
        $sameDiv   = $this->createMember(['division_id' => $home->id, 'unit_id' => null]);
        $otherDiv  = $this->createMember(['division_id' => $other->id, 'unit_id' => null]);
        $hierarchy = app(UnitHierarchy::class);

        $this->assertTrue($hierarchy->sharesLedUnit($leader, $sameDiv, UnitLevel::Platoon));
        $this->assertFalse($hierarchy->sharesLedUnit($leader, $otherDiv, UnitLevel::Platoon));
    }
}
