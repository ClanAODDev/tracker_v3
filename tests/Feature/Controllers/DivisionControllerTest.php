<?php

namespace Tests\Feature\Controllers;

use App\Enums\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function show_displays_division_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer)
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/show')
                ->where('division.name', $division->name)
                ->has('division.applicationsUrl')
                ->has('toolbar')
                ->where('toolbar', fn ($tools) => collect($tools)->pluck('key')->contains('members'))
                ->has('stats.memberCount')
                ->has('census.population')
                ->has('platoons'));
    }

    #[Test]
    public function members_page_renders_inertia_with_member_rows()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer)
            ->get(route('division.members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/members')
                ->where('division.slug', $division->slug)
                ->where('members', fn ($members) => collect($members)->contains('id', $member->guid))
                ->has('unitStats.totalCount')
                ->has('bulk.urls.tags')
                ->has('tagFilter'));
    }

    #[Test]
    public function show_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('division', $division->slug));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function part_time_page_lists_part_time_members()
    {
        $officer   = $this->createOfficer();
        $division  = $officer->member->division;
        $partTimer = $this->createMember();
        $division->partTimeMembers()->attach($partTimer->id);

        $this->actingAs($officer)
            ->get(route('partTimers', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/part-time')
                ->where('stats.total', 1)
                ->where('members', fn ($members) => collect($members)->contains('clanId', $partTimer->clan_id))
                ->has('addUrl'));
    }

    #[Test]
    public function admins_get_a_link_to_assign_division_leadership()
    {
        $division = $this->createActiveDivision();

        $this->actingAs($this->createAdmin())
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('division.leadershipUrl', fn (string $url) => str_ends_with($url, "/admin/divisions/{$division->slug}/edit#leadership")));
    }

    #[Test]
    public function officers_do_not_get_a_link_to_assign_division_leadership()
    {
        $officer = $this->createOfficer();

        $this->actingAs($officer)
            ->get(route('division', $officer->member->division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.leadershipUrl', null));
    }

    #[Test]
    public function division_editors_get_a_link_to_the_notifications_tab()
    {
        $division = $this->createActiveDivision();

        $this->actingAs($this->createAdmin())
            ->get(route('division', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('division.notificationsUrl', fn (string $url) => str_ends_with($url, "/operations/divisions/{$division->slug}/edit?tab=notifications%3A%3Adata%3A%3Atab"))
                ->where('division.hasChatAlerts', false));
    }

    #[Test]
    public function notifications_count_as_configured_once_an_alert_posts_to_a_channel()
    {
        $division = $this->createActiveDivision();
        $division->settings()->set('chat_alerts', array_merge($division->settings()->get('chat_alerts'), ['member_created' => 'officers']));

        $this->actingAs($this->createAdmin())
            ->get(route('division', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.hasChatAlerts', true));
    }

    #[Test]
    public function the_divisions_own_leaders_get_a_link_to_the_notifications_tab()
    {
        $executiveOfficer = $this->createOfficer();

        $this->actingAs($executiveOfficer)
            ->get(route('division', $executiveOfficer->member->division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.notificationsUrl', fn ($url) => is_string($url)));
    }

    #[Test]
    public function officers_without_a_leadership_position_do_not_get_a_link_to_the_notifications_tab()
    {
        $officer = $this->createOfficer(memberAttributes: ['position' => Position::MEMBER]);

        $this->actingAs($officer)
            ->get(route('division', $officer->member->division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('division.notificationsUrl', null));
    }
}
