<?php

namespace Tests\Unit\Services;

use App\Exceptions\RecruitmentFailedException;
use App\Models\DivisionUnitLevel;
use App\Models\Handle;
use App\Models\Member;
use App\Models\Unit;
use App\Services\RecruitmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class RecruitmentServiceTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    protected RecruitmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RecruitmentService;
    }

    #[Test]
    public function create_member_creates_new_member(): void
    {
        $division  = $this->createActiveDivision();
        $platoon   = $this->createPlatoon($division);
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $member = $this->service->createMember(
            12345,
            'TestMember',
            $division,
            1,
            $platoon->id,
            [],
            $recruiter
        );

        $this->assertDatabaseHas('members', [
            'clan_id'     => 12345,
            'name'        => 'TestMember',
            'division_id' => $division->id,
            'unit_id'     => $platoon->id,
        ]);
    }

    #[Test]
    public function create_member_can_place_a_recruit_in_a_deeper_unit(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $platoon   = $this->createPlatoon($division);
        $middle    = Unit::factory()->childOf($platoon)->create();
        $team      = Unit::factory()->childOf($middle)->create();
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $member = $this->service->createMember(12345, 'TestMember', $division, 1, $team->id, [], $recruiter);

        $this->assertSame($team->id, $member->unit_id);
    }

    #[Test]
    public function create_member_in_a_flat_division_needs_no_unit(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->delete();
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $member = $this->service->createMember(12345, 'TestMember', $division->fresh(), 1, null, [], $recruiter);

        $this->assertSame($division->id, $member->division_id);
        $this->assertNull($member->unit_id);
    }

    #[Test]
    public function create_member_rejects_member_already_in_a_division(): void
    {
        $division  = $this->createActiveDivision();
        $platoon   = $this->createPlatoon($division);
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $existingMember = Member::factory()->create([
            'clan_id'     => 54321,
            'name'        => 'OldName',
            'division_id' => $division->id,
        ]);

        $this->expectException(RecruitmentFailedException::class);

        $this->service->createMember(
            54321,
            'NewName',
            $division,
            1,
            $platoon->id,
            [],
            $recruiter
        );

        $this->assertEquals('OldName', $existingMember->fresh()->name);
    }

    #[Test]
    public function create_member_reactivates_member_with_no_division(): void
    {
        $division  = $this->createActiveDivision();
        $platoon   = $this->createPlatoon($division);
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $exMember = Member::factory()->create([
            'clan_id'     => 54321,
            'name'        => 'OldName',
            'division_id' => 0,
        ]);

        $member = $this->service->createMember(
            54321,
            'NewName',
            $division,
            1,
            $platoon->id,
            [],
            $recruiter
        );

        $this->assertEquals($exMember->id, $member->id);
        $this->assertEquals('NewName', $member->fresh()->name);
        $this->assertEquals($division->id, $member->fresh()->division_id);
    }

    #[Test]
    public function create_member_rejects_unit_from_another_division(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $otherPlatoon  = $this->createPlatoon($otherDivision);
        $recruiter     = Member::factory()->create(['clan_id' => 99999]);

        $this->expectException(RecruitmentFailedException::class);

        $this->service->createMember(
            54321,
            'NewName',
            $division,
            1,
            $otherPlatoon->id,
            [],
            $recruiter
        );
    }

    #[Test]
    public function create_member_attaches_ingame_handle(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $handle   = Handle::factory()->create();
        $division->handles()->sync([$handle->id]);
        $recruiter = Member::factory()->create(['clan_id' => 99999]);

        $member = $this->service->createMember(
            11111,
            'HandleTest',
            $division,
            1,
            $platoon->id,
            [$handle->id => 'MyGameHandle'],
            $recruiter
        );

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => [$handle->id => 'MyGameHandle'],
        ]);
    }

    #[Test]
    public function create_member_request_creates_pending_request(): void
    {
        $division  = $this->createActiveDivision();
        $member    = Member::factory()->create(['division_id' => $division->id]);
        $requester = Member::factory()->create();

        $this->service->createMemberRequest($member, $division, $requester);

        $this->assertDatabaseHas('member_requests', [
            'member_id'    => $member->id,
            'division_id'  => $division->id,
            'requester_id' => $requester->id,
        ]);
    }

    #[Test]
    public function create_member_request_skips_if_pending_exists(): void
    {
        $division   = $this->createActiveDivision();
        $member     = Member::factory()->create(['division_id' => $division->id]);
        $requester1 = Member::factory()->create();
        $requester2 = Member::factory()->create();

        $this->service->createMemberRequest($member, $division, $requester1);
        $this->service->createMemberRequest($member, $division, $requester2);

        $this->assertDatabaseCount('member_requests', 1);
    }
}
