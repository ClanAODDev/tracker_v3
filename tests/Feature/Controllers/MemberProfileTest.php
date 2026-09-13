<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\Award;
use App\Models\MemberAward;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberProfileTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function show_requires_authentication()
    {
        $member = $this->createMember();

        $this->get(route('member', $member->getUrlParams()))->assertRedirect('/login');
    }

    #[Test]
    public function show_renders_the_inertia_profile_page()
    {
        $viewer = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $viewer->member->division_id]);

        Note::factory()->create(['member_id' => $member->id, 'author_id' => $viewer->id, 'type' => 'positive']);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('member/show')
                ->where('member.clanId', $member->clan_id)
                ->where('member.name', $member->name)
                ->has('breadcrumbs')
                ->has('stats.tenure')
                ->has('stats.activity')
                ->has('rankTimeline.nodes')
                ->has('noteTypes')
                ->where('notes', fn ($notes) => count($notes) === 1));
    }

    #[Test]
    public function notes_are_hidden_from_users_who_cannot_create_them()
    {
        $viewer = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('canCreateNote', false)
                ->where('notes', []));
    }

    #[Test]
    public function tag_management_is_exposed_to_a_senior_leader()
    {
        $viewer = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $viewer->member->division_id]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('tagManagement.addUrl')
                ->has('tagManagement.removeUrl')
                ->where('tagManagement.canCreate', true));
    }

    #[Test]
    public function tag_management_is_withheld_from_a_plain_member()
    {
        $viewer = $this->createMemberWithUser(['rank' => Rank::RECRUIT]);
        $member = $this->createMember(['rank' => Rank::RECRUIT]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('tagManagement', null));
    }

    #[Test]
    public function tiered_awards_are_grouped_with_all_earned_tiers()
    {
        $viewer   = $this->createSeniorLeader();
        $member   = $this->createMember(['division_id' => $viewer->member->division_id]);
        $division = $this->createActiveDivision();

        $tier1 = Award::factory()->create(['division_id' => $division->id, 'name' => 'Tier 1']);
        $tier2 = Award::factory()->create([
            'division_id'           => $division->id,
            'name'                  => 'Tier 2',
            'prerequisite_award_id' => $tier1->id,
        ]);
        $tier3 = Award::factory()->create([
            'division_id'           => $division->id,
            'name'                  => 'Tier 3',
            'prerequisite_award_id' => $tier2->id,
        ]);

        foreach ([$tier1, $tier2, $tier3] as $tier) {
            MemberAward::factory()->approved()->create(['member_id' => $member->id, 'award_id' => $tier->id]);
        }

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('awards.list', 1)
                ->where('awards.list.0.name', 'Tier 3')
                ->has('awards.list.0.tiers', 3));
    }
}
