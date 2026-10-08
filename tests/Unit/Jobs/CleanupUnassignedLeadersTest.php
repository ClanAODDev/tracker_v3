<?php

namespace Tests\Unit\Jobs;

use App\Enums\Position;
use App\Jobs\CleanupUnassignedLeaders;
use App\Services\Units\UnitAssignment;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class CleanupUnassignedLeadersTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function resets_unassigned_squad_leaders_to_member()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::SQUAD_LEADER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    #[Test]
    public function resets_unassigned_platoon_leaders_to_member()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::PLATOON_LEADER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    #[Test]
    public function does_not_affect_assigned_squad_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $leader = $this->createSquadLeader($squad);

        (new CleanupUnassignedLeaders)->handle();

        $leader->refresh();

        $this->assertEquals(Position::SQUAD_LEADER, $leader->position);
    }

    #[Test]
    public function does_not_affect_assigned_platoon_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $leader = $this->createPlatoonLeader($platoon);

        (new CleanupUnassignedLeaders)->handle();

        $leader->refresh();

        $this->assertEquals(Position::PLATOON_LEADER, $leader->position);
    }

    #[Test]
    public function does_not_affect_regular_members()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    #[Test]
    public function does_not_affect_higher_positions()
    {
        $division = $this->createActiveDivision();

        $xo = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::EXECUTIVE_OFFICER,
        ]);

        $co = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $xo->refresh();
        $co->refresh();

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $xo->position);
        $this->assertEquals(Position::COMMANDING_OFFICER, $co->position);
    }

    #[Test]
    public function job_is_queueable()
    {
        $job = new CleanupUnassignedLeaders;

        $this->assertTrue(in_array(
            Queueable::class,
            class_uses_recursive($job)
        ));
    }

    #[Test]
    public function clears_a_platoon_leader_who_left_the_division()
    {
        $platoon = $this->createPlatoon();
        $leader  = $this->createPlatoonLeader($platoon);
        $leader->update(['division_id' => 0, 'unit_id' => null, 'position' => Position::MEMBER]);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertNull($platoon->fresh()->leader_id);
    }

    #[Test]
    public function clears_a_platoon_leader_who_became_an_executive_officer_and_keeps_them_xo()
    {
        $platoon = $this->createPlatoon();
        $leader  = $this->createPlatoonLeader($platoon);
        $leader->update(['position' => Position::EXECUTIVE_OFFICER, 'unit_id' => null]);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertNull($platoon->fresh()->leader_id);
        $this->assertEquals(Position::EXECUTIVE_OFFICER, $leader->fresh()->position);
    }

    #[Test]
    public function clears_a_squad_leader_assigned_to_a_different_squad_and_returns_them_to_member()
    {
        $platoon = $this->createPlatoon();
        $squad   = $this->createSquad($platoon);
        $other   = $this->createSquad($platoon);
        $leader  = $this->createSquadLeader($squad);
        $leader->update(['unit_id' => $other->id]);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertNull($squad->fresh()->leader_id);
        $this->assertEquals(Position::MEMBER, $leader->fresh()->position);
    }

    #[Test]
    public function clears_a_squad_leader_who_moved_to_another_division()
    {
        $squad  = $this->createSquad($this->createPlatoon());
        $leader = $this->createSquadLeader($squad);
        $leader->update(['division_id' => $this->createActiveDivision()->id]);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertNull($squad->fresh()->leader_id);
    }

    #[Test]
    public function keeps_leaders_who_still_fit()
    {
        $platoon = $this->createPlatoon();
        $squad   = $this->createSquad($platoon);
        $pl      = $this->createPlatoonLeader($platoon);
        $sl      = $this->createSquadLeader($squad);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertSame($pl->clan_id, $platoon->fresh()->leader_id);
        $this->assertSame($sl->clan_id, $squad->fresh()->leader_id);
        $this->assertEquals(Position::PLATOON_LEADER, $pl->fresh()->position);
        $this->assertEquals(Position::SQUAD_LEADER, $sl->fresh()->position);
    }

    #[Test]
    public function a_leader_of_an_archived_unit_returns_to_member()
    {
        $platoon = $this->createPlatoon();
        $squad   = $this->createSquad($platoon);
        $pl      = $this->createPlatoonLeader($platoon);
        $sl      = $this->createSquadLeader($squad);
        $units   = app(UnitAssignment::class);
        $units->archive($squad);
        $units->archive($platoon);

        (new CleanupUnassignedLeaders)->handle();

        $this->assertEquals(Position::MEMBER, $pl->fresh()->position);
        $this->assertEquals(Position::MEMBER, $sl->fresh()->position);
    }
}
