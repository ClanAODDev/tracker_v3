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

    #[Test]
    public function flag_action_offers_flag_for_a_member_not_yet_flagged()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['flagged_for_inactivity' => false]);

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member) {
                $page->component('member/show');
                $action = collect($page->toArray()['props']['actions'])->firstWhere('label', 'Flag for inactivity');
                $this->assertNotNull($action);
                $this->assertSame(route('member.flag-inactive', $member->clan_id), $action['url']);
            });
    }

    #[Test]
    public function flag_action_offers_unflag_for_an_already_flagged_member()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['flagged_for_inactivity' => true]);

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($member) {
                $page->component('member/show');
                $labels = collect($page->toArray()['props']['actions'])->pluck('label');
                $this->assertFalse($labels->contains('Flag for inactivity'));

                $action = collect($page->toArray()['props']['actions'])->firstWhere('label', 'Unflag for inactivity');
                $this->assertNotNull($action);
                $this->assertSame(route('member.unflag-inactive', $member->clan_id), $action['url']);
            });
    }
}
