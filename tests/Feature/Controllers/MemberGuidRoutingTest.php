<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Jobs\UpdateRankForMember;
use App\Models\Award;
use App\Models\MemberAward;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberGuidRoutingTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function member_urls_use_the_guid()
    {
        $member = $this->createMember(['name' => 'ReaverVI', 'rank' => Rank::SERGEANT]);

        $this->assertSame(url("members/{$member->guid}-Sgt-ReaverVI"), route('member', $member->getUrlParams()));
    }

    #[Test]
    public function the_profile_resolves_by_guid()
    {
        $user   = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('member/show'));
    }

    #[Test]
    public function a_legacy_forum_id_profile_link_redirects_permanently_to_the_guid_url()
    {
        $user   = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($user)
            ->get("/members/{$member->clan_id}-Sgt-Whoever")
            ->assertStatus(301)
            ->assertRedirect(route('member', $member->getUrlParams()));
    }

    #[Test]
    public function a_bare_legacy_forum_id_link_redirects_to_the_guid_url()
    {
        $user   = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($user)
            ->get("/members/{$member->clan_id}")
            ->assertStatus(301)
            ->assertRedirect(route('member', $member->getUrlParams()));
    }

    #[Test]
    public function a_member_internal_id_is_not_treated_as_a_member_reference()
    {
        $user   = $this->createMemberWithUser();
        $member = $this->createMember(['clan_id' => 900001]);

        $this->actingAs($user)
            ->get("/members/{$member->id}-Sgt-Whoever")
            ->assertNotFound();
    }

    #[Test]
    public function an_unknown_guid_is_not_found()
    {
        $this->actingAs($this->createMemberWithUser())
            ->get('/members/zzzzzzzzzz-Sgt-Nobody')
            ->assertNotFound();
    }

    #[Test]
    public function newly_signed_promotion_links_use_the_guid_and_validate()
    {
        Bus::fake();

        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $action   = RankAction::factory()->pending()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $url = URL::temporarySignedRoute('promotion.accept', now()->addMinutes(10), [$member, $action]);

        $this->assertStringContainsString("/members/{$member->guid}/promotion/", $url);

        $this->post($url)->assertOk();

        Bus::assertDispatched(UpdateRankForMember::class);
    }

    #[Test]
    public function an_award_request_identifies_the_member_by_guid()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $target   = $this->createMember(['division_id' => $division->id]);
        $award    = Award::factory()->create(['division_id' => $division->id, 'allow_request' => true]);

        $this->actingAs($user)
            ->post(route('awards.store-recommendation', $award), ['member' => $target->guid, 'reason' => 'Carried the op'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas(MemberAward::class, ['member_id' => $target->id, 'award_id' => $award->id]);
    }

    #[Test]
    public function an_award_request_rejects_a_forum_id()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $target   = $this->createMember(['division_id' => $division->id]);
        $award    = Award::factory()->create(['division_id' => $division->id, 'allow_request' => true]);

        $this->actingAs($user)
            ->post(route('awards.store-recommendation', $award), ['member' => (string) $target->clan_id, 'reason' => 'Carried the op'])
            ->assertSessionHasErrors('member');

        $this->assertDatabaseMissing(MemberAward::class, ['member_id' => $target->id, 'award_id' => $award->id]);
    }
}
