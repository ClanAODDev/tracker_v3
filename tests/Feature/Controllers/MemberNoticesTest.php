<?php

namespace Tests\Feature\Controllers;

use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberNoticesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function leave_notice_is_hidden_for_regular_member()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $viewer   = $this->createMemberWithUser(['division_id' => $division->id]);

        Leave::factory()->create(['member_id' => $member->id]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertDontSee('leave of absence');
    }

    #[Test]
    public function leave_notice_is_visible_for_sr_ldr()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $srLdr    = $this->createSeniorLeader($division);

        Leave::factory()->create(['member_id' => $member->id]);

        $this->actingAs($srLdr)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertSee('leave of absence');
    }

    #[Test]
    public function handle_notice_does_not_crash_for_divisionless_member()
    {
        $member = $this->createMember(['division_id' => 0]);
        $srLdr  = $this->createSeniorLeader();

        $this->actingAs($srLdr)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk();
    }
}
