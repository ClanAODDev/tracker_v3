<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class InactiveMembersPageTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_renders_the_inactive_members_inertia_page()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $inactive = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => now()->subYear(),
        ]);
        $flagged = $this->createMember([
            'division_id'            => $division->id,
            'flagged_for_inactivity' => true,
            'last_voice_activity'    => now()->subYear(),
        ]);

        $this->actingAs($officer)
            ->get(route('division.inactive-members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/inactive-members')
                ->where('inactive', fn ($rows) => collect($rows)->contains('id', $inactive->clan_id))
                ->where('flagged', fn ($rows) => collect($rows)->contains('id', $flagged->clan_id))
                ->has('stats.total')
                ->has('platoons')
                ->has('bulk.flag'));
    }

    #[Test]
    public function activity_log_shows_who_flagged_a_member()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $target   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer)->get(route('member.flag-inactive', $target->clan_id));

        $this->actingAs($officer)
            ->get(route('division.inactive-members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('activityLog', fn ($rows) => collect($rows)->contains(
                    fn ($row) => $row['verb'] === 'flagged' && $row['user'] === $officer->name && $row['subject'] === $target->name,
                )));
    }
}
