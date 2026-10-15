<?php

namespace Tests\Feature\Services\Units;

use App\Enums\Position;
use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitPositionSyncTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private $division;

    private Unit $platoon;

    private Unit $squad;

    private UnitAssignment $units;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = $this->createActiveDivision();
        $this->platoon  = $this->createPlatoon($this->division);
        $this->squad    = $this->createSquad($this->platoon);
        $this->units    = app(UnitAssignment::class);
    }

    private function member(array $attributes = [])
    {
        return $this->createMember(['division_id' => $this->division->id, ...$attributes]);
    }

    #[Test]
    public function setting_a_leader_derives_the_position_from_the_unit_tier(): void
    {
        $platoonLeader = $this->member();
        $squadLeader   = $this->member();

        $this->units->setLeader($this->platoon, $platoonLeader->clan_id);
        $this->units->setLeader($this->squad, $squadLeader->clan_id);

        $this->assertSame(Position::PLATOON_LEADER, $platoonLeader->fresh()->position);
        $this->assertSame(Position::SQUAD_LEADER, $squadLeader->fresh()->position);
    }

    #[Test]
    public function replacing_or_removing_a_leader_returns_the_previous_leader_to_member(): void
    {
        $first  = $this->member();
        $second = $this->member();

        $this->units->setLeader($this->squad, $first->clan_id);
        $this->units->setLeader($this->squad, $second->clan_id);

        $this->assertSame(Position::MEMBER, $first->fresh()->position);
        $this->assertSame(Position::SQUAD_LEADER, $second->fresh()->position);

        $this->units->setLeader($this->squad, null);

        $this->assertSame(Position::MEMBER, $second->fresh()->position);
    }

    #[Test]
    public function moving_a_leader_to_another_unit_updates_the_position(): void
    {
        $leader = $this->member();

        $this->units->setLeader($this->squad, $leader->clan_id);
        $this->units->setLeader($this->platoon, $leader->clan_id);

        $this->assertSame(Position::PLATOON_LEADER, $leader->fresh()->position);
        $this->assertNull($this->squad->fresh()->leader_id);
    }

    #[Test]
    public function a_middle_tier_unit_leader_is_a_platoon_leader_in_a_deep_division(): void
    {
        DivisionUnitLevel::create(['division_id' => $this->division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $middle = Unit::factory()->childOf($this->platoon)->create();
        $leaf   = Unit::factory()->childOf($middle)->create();

        $middleLeader = $this->member();
        $leafLeader   = $this->member();

        $this->units->setLeader($middle, $middleLeader->clan_id);
        $this->units->setLeader($leaf, $leafLeader->clan_id);

        $this->assertSame(Position::PLATOON_LEADER, $middleLeader->fresh()->position);
        $this->assertSame(Position::SQUAD_LEADER, $leafLeader->fresh()->position);
    }

    #[Test]
    public function commanding_and_executive_officers_keep_their_position_when_leading_a_unit(): void
    {
        $co = $this->member(['position' => Position::COMMANDING_OFFICER]);

        $this->units->setLeader($this->platoon, $co->clan_id);
        $this->assertSame(Position::COMMANDING_OFFICER, $co->fresh()->position);

        $this->units->setLeader($this->platoon, null);
        $this->assertSame(Position::COMMANDING_OFFICER, $co->fresh()->position);
    }

    #[Test]
    public function archiving_a_unit_returns_its_leader_to_member(): void
    {
        $leader = $this->member();
        $this->units->setLeader($this->squad, $leader->clan_id);

        $this->units->archive($this->squad);

        $this->assertSame(Position::MEMBER, $leader->fresh()->position);
    }

    #[Test]
    public function clearing_leadership_returns_the_leader_to_member(): void
    {
        $leader = $this->member();
        $this->units->setLeader($this->squad, $leader->clan_id);

        $this->units->clearLeadership([$leader->clan_id]);

        $this->assertSame(Position::MEMBER, $leader->fresh()->position);
    }
}
