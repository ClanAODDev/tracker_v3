<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ResetOrphanedUnitAssignments;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ResetOrphanedUnitAssignmentsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function resets_the_unit_for_member_with_zero_division()
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));

        $member = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertNull($member->fresh()->unit_id);
    }

    #[Test]
    public function does_not_affect_members_with_valid_division()
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));

        $member = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertSame($squad->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function does_not_affect_members_without_unit_assignments()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember(['division_id' => $division->id, 'unit_id' => null]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertNull($member->fresh()->unit_id);
    }

    #[Test]
    public function job_is_queueable()
    {
        $job = new ResetOrphanedUnitAssignments;

        $this->assertTrue(in_array(
            Queueable::class,
            class_uses_recursive($job)
        ));
    }

    #[Test]
    public function resets_a_member_whose_unit_belongs_to_another_division()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $otherSquad    = $this->createSquad($this->createPlatoon($otherDivision));

        $member = $this->createMember(['division_id' => $division->id, 'unit_id' => $otherSquad->id]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertSame($division->id, $member->division_id);
        $this->assertNull($member->unit_id);
    }

    #[Test]
    public function leaves_consistent_platoon_and_squad_assignments_alone()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $inSquad     = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);
        $platoonOnly = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertSame($squad->id, $inSquad->fresh()->unit_id);
        $this->assertSame($platoon->id, $platoonOnly->fresh()->unit_id);
    }

    #[Test]
    public function resets_members_whose_unit_was_archived()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $platoon->id]);
        $platoon->delete();

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertNull($member->fresh()->unit_id);
    }
}
