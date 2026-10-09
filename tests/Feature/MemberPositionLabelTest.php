<?php

namespace Tests\Feature;

use App\Enums\Position;
use App\Models\DivisionUnitLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberPositionLabelTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_leader_is_labelled_with_their_levels_leader_title(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 1)->update(['leader_title' => 'Company Commander']);
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 2)->update(['leader_title' => 'Team Lead']);

        $platoon = $this->createPlatoon($division->fresh());
        $squad   = $this->createSquad($platoon);

        $this->assertSame('Company Commander', $this->createPlatoonLeader($platoon)->fresh()->positionLabel());
        $this->assertSame('Team Lead', $this->createSquadLeader($squad)->fresh()->positionLabel());
    }

    #[Test]
    public function other_positions_keep_their_enum_label(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'position' => Position::EXECUTIVE_OFFICER]);

        $this->assertSame('Executive Officer', $member->positionLabel());
    }

    #[Test]
    public function a_leader_without_a_unit_keeps_the_enum_label(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'position' => Position::SQUAD_LEADER, 'unit_id' => null]);

        $this->assertSame('Squad Leader', $member->positionLabel());
    }
}
