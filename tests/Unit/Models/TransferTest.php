<?php

namespace Tests\Unit\Models;

use App\Enums\Position;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TransferTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_approve_sets_approved_at()
    {
        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $member = $this->createMember([
            'division_id' => $sourceDivision->id,
            'position' => Position::MEMBER,
        ]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $targetDivision->id,
        ]);

        $transfer->approve();

        $this->assertNotNull($transfer->approved_at);
    }

    public function test_approve_transfers_member_to_new_division()
    {
        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $platoon = $this->createPlatoon($sourceDivision);
        $squad = $this->createSquad($platoon);

        $member = $this->createMember([
            'division_id' => $sourceDivision->id,
            'platoon_id' => $platoon->id,
            'squad_id' => $squad->id,
            'position' => Position::MEMBER,
        ]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $targetDivision->id,
        ]);

        $transfer->approve();
        $member->refresh();

        $this->assertEquals($targetDivision->id, $member->division_id);
        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
        $this->assertEquals(Position::MEMBER, $member->position);
    }

    public function test_approve_removes_squad_leader_assignment()
    {
        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $platoon = $this->createPlatoon($sourceDivision);
        $squad = $this->createSquad($platoon);

        $member = $this->createSquadLeader($squad);

        $this->assertEquals($member->clan_id, $squad->leader_id);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $targetDivision->id,
        ]);

        $transfer->approve();
        $squad->refresh();

        $this->assertEquals(0, $squad->leader_id);
    }

    public function test_approve_removes_platoon_leader_assignment()
    {
        $sourceDivision = $this->createActiveDivision();
        $targetDivision = $this->createActiveDivision();

        $platoon = $this->createPlatoon($sourceDivision);
        $member = $this->createPlatoonLeader($platoon);

        $this->assertEquals($member->clan_id, $platoon->leader_id);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $targetDivision->id,
        ]);

        $transfer->approve();
        $platoon->refresh();

        $this->assertEquals(0, $platoon->leader_id);
    }

    public function test_scope_pending_returns_unapproved_transfers()
    {
        $division = $this->createActiveDivision();
        $member = $this->createMember();

        $pending = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $division->id,
        ]);

        $approved = Transfer::factory()->approved()->create([
            'member_id' => $member->id,
            'division_id' => $division->id,
        ]);

        $results = Transfer::pending()->get();

        $this->assertTrue($results->contains($pending));
        $this->assertFalse($results->contains($approved));
    }

    public function test_member_relationship_returns_correct_member()
    {
        $member = $this->createMember();
        $division = $this->createActiveDivision();

        $transfer = Transfer::factory()->create([
            'member_id' => $member->id,
            'division_id' => $division->id,
        ]);

        $this->assertTrue($transfer->member->is($member));
    }

    public function test_division_relationship_returns_target_division()
    {
        $member = $this->createMember();
        $division = $this->createActiveDivision();

        $transfer = Transfer::factory()->create([
            'member_id' => $member->id,
            'division_id' => $division->id,
        ]);

        $this->assertTrue($transfer->division->is($division));
    }
}
