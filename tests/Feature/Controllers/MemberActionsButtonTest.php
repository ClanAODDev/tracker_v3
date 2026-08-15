<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberActionsButtonTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function sgt_training_link_is_visible_for_qualifying_sr_ldr()
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $member = $this->createMember();

        $this->actingAs($srLdr)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertSee(route('training.sgt', ['clan_id' => $member->clan_id]), false)
            ->assertSee('SGT Training');
    }

    #[Test]
    public function sgt_training_link_is_hidden_for_sr_ldr_at_sergeant_rank()
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::SERGEANT]);
        $member = $this->createMember();

        $this->actingAs($srLdr)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertDontSee('SGT Training');
    }

    #[Test]
    public function sgt_training_link_is_hidden_for_officer()
    {
        $officer = $this->createOfficer(memberAttributes: ['rank' => Rank::MASTER_SERGEANT]);
        $member  = $this->createMember();

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertDontSee('SGT Training');
    }
}
