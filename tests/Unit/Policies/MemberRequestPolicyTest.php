<?php

namespace Tests\Unit\Policies;

use App\Models\MemberRequest;
use App\Policies\MemberRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberRequestPolicyTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private MemberRequestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MemberRequestPolicy;
    }

    #[Test]
    public function sr_ldr_can_update_any_request(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $request  = MemberRequest::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($this->policy->update($srLdr, $request));
    }

    #[Test]
    public function requester_can_update_their_own_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $officer->member->id,
        ]);

        $this->assertTrue($this->policy->update($officer, $request));
    }

    #[Test]
    public function officer_cannot_update_another_officers_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $other    = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $other->member->id,
        ]);

        $this->assertFalse($this->policy->update($officer, $request));
    }

    #[Test]
    public function requester_can_cancel_their_own_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $officer->member->id,
        ]);

        $this->assertTrue($this->policy->cancel($officer, $request));
    }

    #[Test]
    public function officer_cannot_cancel_another_officers_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $other    = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $other->member->id,
        ]);

        $this->assertFalse($this->policy->cancel($officer, $request));
    }

    #[Test]
    public function requester_can_delete_their_own_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $officer->member->id,
        ]);

        $this->assertTrue($this->policy->delete($officer, $request));
    }

    #[Test]
    public function officer_cannot_delete_another_officers_request(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $other    = $this->createOfficer($division);
        $request  = MemberRequest::factory()->create([
            'division_id'  => $division->id,
            'requester_id' => $other->member->id,
        ]);

        $this->assertFalse($this->policy->delete($officer, $request));
    }

    #[Test]
    public function sr_ldr_can_cancel_any_request(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $request  = MemberRequest::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($this->policy->cancel($srLdr, $request));
    }

    #[Test]
    public function sr_ldr_can_delete_any_request(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $request  = MemberRequest::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($this->policy->delete($srLdr, $request));
    }
}
