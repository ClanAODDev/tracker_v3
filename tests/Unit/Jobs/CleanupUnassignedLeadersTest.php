<?php

namespace Tests\Unit\Jobs;

use App\Enums\Position;
use App\Jobs\CleanupUnassignedLeaders;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class CleanupUnassignedLeadersTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_resets_unassigned_squad_leaders_to_member()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position' => Position::SQUAD_LEADER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    public function test_resets_unassigned_platoon_leaders_to_member()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position' => Position::PLATOON_LEADER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    public function test_does_not_affect_assigned_squad_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $squad = $this->createSquad($platoon);

        $leader = $this->createSquadLeader($squad);

        (new CleanupUnassignedLeaders)->handle();

        $leader->refresh();

        $this->assertEquals(Position::SQUAD_LEADER, $leader->position);
    }

    public function test_does_not_affect_assigned_platoon_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);

        $leader = $this->createPlatoonLeader($platoon);

        (new CleanupUnassignedLeaders)->handle();

        $leader->refresh();

        $this->assertEquals(Position::PLATOON_LEADER, $leader->position);
    }

    public function test_does_not_affect_regular_members()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'position' => Position::MEMBER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $member->refresh();

        $this->assertEquals(Position::MEMBER, $member->position);
    }

    public function test_does_not_affect_higher_positions()
    {
        $division = $this->createActiveDivision();

        $xo = $this->createMember([
            'division_id' => $division->id,
            'position' => Position::EXECUTIVE_OFFICER,
        ]);

        $co = $this->createMember([
            'division_id' => $division->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        (new CleanupUnassignedLeaders)->handle();

        $xo->refresh();
        $co->refresh();

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $xo->position);
        $this->assertEquals(Position::COMMANDING_OFFICER, $co->position);
    }

    public function test_job_is_queueable()
    {
        $job = new CleanupUnassignedLeaders;

        $this->assertTrue(in_array(
            \Illuminate\Foundation\Queue\Queueable::class,
            class_uses_recursive($job)
        ));
    }
}
