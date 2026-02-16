<?php

namespace Tests\Unit\Services;

use App\Models\Member;
use App\Services\RecruitmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_create_member_creates_new_member(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $member = $this->service->createMember(
            12345,
            'TestMember',
            $division,
            1,
            $platoon->id,
            null,
            'GameHandle',
            99999
        );

        $this->assertDatabaseHas('members', [
            'clan_id'     => 12345,
            'name'        => 'TestMember',
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);
    }

    public function test_create_member_updates_existing_member(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $existingMember = Member::factory()->create([
            'clan_id' => 54321,
            'name'    => 'OldName',
        ]);

        $member = $this->service->createMember(
            54321,
            'NewName',
            $division,
            1,
            $platoon->id,
            null,
            'GameHandle',
            99999
        );

        $this->assertEquals($existingMember->id, $member->id);
        $this->assertEquals('NewName', $member->fresh()->name);
    }

    public function test_create_member_attaches_ingame_handle(): void
    {
        $division            = $this->createActiveDivision();
        $platoon             = $this->createPlatoon($division);
        $handle              = \App\Models\Handle::factory()->create();
        $division->handle_id = $handle->id;
        $division->save();

        $member = $this->service->createMember(
            11111,
            'HandleTest',
            $division,
            1,
            $platoon->id,
            null,
            'MyGameHandle',
            99999
        );

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => 'MyGameHandle',
        ]);
    }

    public function test_create_member_request_creates_pending_request(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);

        $this->service->createMemberRequest($member, $division, 99999);

        $this->assertDatabaseHas('member_requests', [
            'member_id'    => $member->clan_id,
            'division_id'  => $division->id,
            'requester_id' => 99999,
        ]);
    }

    public function test_create_member_request_skips_if_pending_exists(): void
    {
        $division = $this->createActiveDivision();
        $member   = Member::factory()->create(['division_id' => $division->id]);

        $this->service->createMemberRequest($member, $division, 99999);
        $this->service->createMemberRequest($member, $division, 88888);

        $this->assertDatabaseCount('member_requests', 1);
    }
}
