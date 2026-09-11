<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberAward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AwardControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_renders_the_gallery_with_award_sections(): void
    {
        $user     = User::factory()->create();
        $division = Division::factory()->create();
        Award::factory()->create(['division_id' => $division->id, 'name' => $division->name . ' - Trailblazer']);
        Award::factory()->global()->create(['name' => 'Bug Hunter']);

        $this->actingAs($user)
            ->get(route('awards.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('awards/index')
                ->has('totals.awards')
                ->has('rarityBreakdown')
                ->has('clan.awards', 1)
                ->has('divisionSections', 1));
    }

    #[Test]
    public function show_renders_an_award_with_its_recipients(): void
    {
        $user   = User::factory()->create();
        $award  = Award::factory()->global()->create();
        $member = Member::factory()->create(['division_id' => Division::factory()->create()->id]);
        MemberAward::factory()->create(['award_id' => $award->id, 'member_id' => $member->id, 'approved' => true]);

        $this->actingAs($user)
            ->get(route('awards.show', $award))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('awards/show')
                ->where('award.id', $award->id)
                ->where('stats.total', 1)
                ->has('recipients.data', 1));
    }

    #[Test]
    public function tiered_recipient_count_counts_each_member_once_across_all_tiers(): void
    {
        $user = User::factory()->create();

        $tier1 = Award::factory()->global()->create([
            'name'              => 'Test Tenure I',
            'tiered_group_name' => 'Test Tenure',
            'display_order'     => 1,
        ]);
        $tier2 = Award::factory()->global()->create([
            'name'                  => 'Test Tenure II',
            'display_order'         => 2,
            'prerequisite_award_id' => $tier1->id,
        ]);

        $division        = Division::factory()->create();
        $memberBothTiers = Member::factory()->create(['division_id' => $division->id]);
        $memberOneTier   = Member::factory()->create(['division_id' => $division->id]);
        $exAodMember     = Member::factory()->create(['division_id' => 0]);

        MemberAward::factory()->create(['award_id' => $tier1->id, 'member_id' => $memberBothTiers->id, 'approved' => true]);
        MemberAward::factory()->create(['award_id' => $tier2->id, 'member_id' => $memberBothTiers->id, 'approved' => true]);
        MemberAward::factory()->create(['award_id' => $tier1->id, 'member_id' => $memberOneTier->id, 'approved' => true]);
        MemberAward::factory()->create(['award_id' => $tier1->id, 'member_id' => $exAodMember->id, 'approved' => true]);

        $this->actingAs($user)
            ->get(route('awards.tiered', 'test-tenure'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('awards/tiered')
                ->where('stats.totalRecipients', 2));
    }
}
