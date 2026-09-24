<?php

namespace Tests\Feature\Controllers;

use App\Enums\DivisionMemberFieldType;
use App\Enums\Rank;
use App\Models\ActivityReminder;
use App\Models\Award;
use App\Models\DivisionMemberField;
use App\Models\Handle;
use App\Models\MemberAward;
use App\Models\Note;
use App\Models\User;
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

    #[Test]
    public function an_officer_can_remind_a_member_with_no_existing_reminders()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['division_id' => $officer->member->division_id]);

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.activity.canRemind', true)
                ->where('stats.activity.remindedToday', false)
                ->has('stats.activity.remindUrl'));
    }

    #[Test]
    public function reminded_today_is_true_once_a_reminder_was_sent_today()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMember(['division_id' => $officer->member->division_id]);

        ActivityReminder::create([
            'member_id'      => $member->id,
            'division_id'    => $member->division_id,
            'reminded_by_id' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.activity.remindedToday', true));
    }

    #[Test]
    public function a_plain_member_cannot_remind_anyone()
    {
        $viewer = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('stats.activity.canRemind', false));
    }

    #[Test]
    public function achievements_are_ordered_most_recently_earned_first()
    {
        $viewer = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $viewer->member->division_id]);

        $older = Award::factory()->create(['name' => 'Older Award']);
        $newer = Award::factory()->create(['name' => 'Newer Award']);

        MemberAward::factory()->approved()->create([
            'member_id'  => $member->id,
            'award_id'   => $older->id,
            'created_at' => now()->subYears(2),
        ]);
        MemberAward::factory()->approved()->create([
            'member_id'  => $member->id,
            'award_id'   => $newer->id,
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('awards.list', 2)
                ->where('awards.list.0.name', 'Newer Award')
                ->where('awards.list.1.name', 'Older Award'));
    }

    #[Test]
    public function custom_field_values_are_shown_to_any_viewer()
    {
        $viewer   = $this->createMemberWithUser();
        $division = $viewer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'role',
            'label'       => 'Role',
            'type'        => DivisionMemberFieldType::SELECT,
            'options'     => [['value' => 'Tank', 'color' => 'blue']],
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $field->id, 'value' => 'Tank']);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customFields.0.key', 'role')
                ->where('customFields.0.label', 'Role')
                ->where('customFields.0.value', 'Tank')
                ->where('customFields.0.color', 'blue')
                ->where('customFields.0.canEdit', false));
    }

    #[Test]
    public function unset_fields_still_appear_in_custom_fields_for_editors_to_fill_in()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'zone',
            'label'       => 'Zone',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($officer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customFields.0.key', 'zone')
                ->where('customFields.0.value', null)
                ->where('customFields.0.canEdit', true));
    }

    #[Test]
    public function a_member_can_edit_their_own_self_editable_field_but_not_a_locked_one()
    {
        $division = $this->createActiveDivision();
        $viewer   = $this->createMemberWithUser(['division_id' => $division->id]);
        DivisionMemberField::create([
            'division_id'   => $division->id,
            'key'           => 'zone',
            'label'         => 'Zone',
            'type'          => DivisionMemberFieldType::TEXT,
            'self_editable' => true,
        ]);
        DivisionMemberField::create([
            'division_id'   => $division->id,
            'key'           => 'rating',
            'label'         => 'Rating',
            'type'          => DivisionMemberFieldType::TEXT,
            'self_editable' => false,
        ]);

        $this->actingAs($viewer)
            ->get(route('member', $viewer->member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('customFields', fn ($fields) => collect($fields)->firstWhere('key', 'zone')['canEdit'] === true
                    && collect($fields)->firstWhere('key', 'rating')['canEdit'] === false));
    }

    #[Test]
    public function details_management_is_null_for_an_unrelated_viewer()
    {
        $viewer = $this->createMemberWithUser();
        $member = $this->createMember();

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('detailsManagement', null));
    }

    #[Test]
    public function details_management_allows_handle_editing_on_your_own_profile()
    {
        $viewer = $this->createMemberWithUser();

        $this->actingAs($viewer)
            ->get(route('member', $viewer->member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('detailsManagement.canEditHandles', true));
    }

    #[Test]
    public function details_management_allows_handle_editing_for_a_senior_leader_over_others()
    {
        $viewer = $this->createSeniorLeader();
        $member = $this->createMember(['division_id' => $viewer->member->division_id]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('detailsManagement.canEditHandles', true));
    }

    #[Test]
    public function missing_required_handle_notice_opens_the_inline_editor_instead_of_linking_to_operations()
    {
        $requiredHandle = Handle::factory()->create();
        $division       = $this->createActiveDivision(['handle_id' => $requiredHandle->id]);
        $platoon        = $this->createPlatoon($division);
        $squad          = $this->createSquad($platoon);
        $leader         = $this->createSquadLeader($squad);
        $viewer         = User::factory()->create(['member_id' => $leader->id, 'name' => $leader->name]);
        $member         = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('notices', fn ($notices) => collect($notices)->contains(
                    fn ($n) => ($n['ctaAction'] ?? null) === 'edit-handles' && ! isset($n['ctaUrl'])
                )));
    }
}
