<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ResetOrphanedUnitAssignments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ResetOrphanedUnitAssignmentsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_resets_platoon_and_squad_for_member_with_zero_division()
    {
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $squad = $this->createSquad($platoon);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id' => $platoon->id,
            'squad_id' => $squad->id,
        ]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
    }

    public function test_does_not_affect_members_with_valid_division()
    {
        $division = $this->createActiveDivision();
        $platoon = $this->createPlatoon($division);
        $squad = $this->createSquad($platoon);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id' => $platoon->id,
            'squad_id' => $squad->id,
        ]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals($platoon->id, $member->platoon_id);
        $this->assertEquals($squad->id, $member->squad_id);
    }

    public function test_does_not_affect_members_without_unit_assignments()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id' => 0,
            'squad_id' => 0,
        ]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
    }

    public function test_job_is_queueable()
    {
        $job = new ResetOrphanedUnitAssignments;

        $this->assertTrue(in_array(
            \Illuminate\Foundation\Queue\Queueable::class,
            class_uses_recursive($job)
        ));
    }
}
