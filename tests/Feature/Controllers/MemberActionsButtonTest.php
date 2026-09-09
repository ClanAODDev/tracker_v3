<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberActionsButtonTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function assertHasSgtTraining($user, $member, bool $expected): void
    {
        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($expected) {
                $page->component('member/show');
                $labels = collect($page->toArray()['props']['actions'])->pluck('label');
                $this->assertSame($expected, $labels->contains('SGT training'));
            });
    }

    #[Test]
    public function sgt_training_action_is_visible_for_qualifying_sr_ldr()
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $member = $this->createMember(['rank' => Rank::SERGEANT]);

        $this->assertHasSgtTraining($srLdr, $member, true);
    }

    #[Test]
    public function sgt_training_action_is_hidden_for_member_below_sergeant()
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $member = $this->createMember(['rank' => Rank::CORPORAL]);

        $this->assertHasSgtTraining($srLdr, $member, false);
    }

    #[Test]
    public function sgt_training_action_is_hidden_for_sr_ldr_at_sergeant_rank()
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::SERGEANT]);
        $member = $this->createMember();

        $this->assertHasSgtTraining($srLdr, $member, false);
    }

    #[Test]
    public function sgt_training_action_is_hidden_for_officer()
    {
        $officer = $this->createOfficer(memberAttributes: ['rank' => Rank::MASTER_SERGEANT]);
        $member  = $this->createMember();

        $this->assertHasSgtTraining($officer, $member, false);
    }
}
