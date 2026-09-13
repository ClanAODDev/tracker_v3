<?php

namespace Tests\Feature\Controllers;

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
                ->where('members', fn ($members) => collect($members)->contains('id', $member->clan_id))
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
}
