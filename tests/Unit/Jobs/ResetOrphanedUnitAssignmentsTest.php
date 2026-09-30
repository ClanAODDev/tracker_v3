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
    public function resets_platoon_and_squad_for_member_with_zero_division()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
        ]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
    }

    #[Test]
    public function does_not_affect_members_with_valid_division()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
        ]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals($platoon->id, $member->platoon_id);
        $this->assertEquals($squad->id, $member->squad_id);
    }

    #[Test]
    public function does_not_affect_members_without_unit_assignments()
    {
        $division = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => 0,
            'squad_id'    => 0,
        ]);

        $member->update(['division_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $member->refresh();

        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
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
    public function resets_a_member_whose_platoon_belongs_to_another_division()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $otherPlatoon  = $this->createPlatoon($otherDivision);
        $otherSquad    = $this->createSquad($otherPlatoon);

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $otherPlatoon->id,
            'squad_id'    => $otherSquad->id,
        ]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertUnassignedInDivision($member, $division->id);
    }

    #[Test]
    public function resets_a_member_whose_platoon_no_longer_exists()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => 999999, 'squad_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertUnassignedInDivision($member, $division->id);
    }

    #[Test]
    public function resets_a_member_whose_squad_no_longer_exists()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => 999999]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertUnassignedInDivision($member, $division->id);
    }

    #[Test]
    public function resets_a_member_whose_squad_belongs_to_a_different_platoon()
    {
        $division   = $this->createActiveDivision();
        $platoon    = $this->createPlatoon($division);
        $otherSquad = $this->createSquad($this->createPlatoon($division));

        $member = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $otherSquad->id,
        ]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertUnassignedInDivision($member, $division->id);
    }

    #[Test]
    public function resets_a_member_with_a_squad_but_no_platoon()
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));
        $member   = $this->createMember(['division_id' => $division->id, 'platoon_id' => 0, 'squad_id' => $squad->id]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertUnassignedInDivision($member, $division->id);
    }

    #[Test]
    public function leaves_consistent_platoon_only_and_squad_assignments_alone()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $inSquad     = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => $squad->id]);
        $platoonOnly = $this->createMember(['division_id' => $division->id, 'platoon_id' => $platoon->id, 'squad_id' => 0]);

        (new ResetOrphanedUnitAssignments)->handle();

        $this->assertSame([$platoon->id, $squad->id], [$inSquad->fresh()->platoon_id, $inSquad->fresh()->squad_id]);
        $this->assertSame([$platoon->id, 0], [$platoonOnly->fresh()->platoon_id, $platoonOnly->fresh()->squad_id]);
    }

    private function assertUnassignedInDivision($member, int $divisionId): void
    {
        $member->refresh();

        $this->assertSame($divisionId, $member->division_id);
        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
    }
}
