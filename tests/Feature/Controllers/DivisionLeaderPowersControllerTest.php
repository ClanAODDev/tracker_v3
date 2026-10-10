<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionLeaderPowersControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_lists_each_level_with_its_tier_and_powers(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer)
            ->get(route('division.leader-powers', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/leader-powers')
                ->where('division.slug', $division->slug)
                ->has('levels', 2)
                ->where('levels.0.depth', 1)
                ->where('levels.0.tier', 'platoon')
                ->where('levels.0.title', $division->topLeaderTitle())
                ->has('levels.0.powers')
                ->where('levels.1.depth', 2)
                ->where('levels.1.tier', 'squad')
                ->where('levels.1.title', $division->bottomLeaderTitle()));
    }

    #[Test]
    public function it_uses_the_divisions_leader_titles_and_abbreviations(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $division->unitLevels()->where('depth', 1)->first()->update(['leader_title' => 'Company Commander']);

        $this->actingAs($officer)
            ->get(route('division.leader-powers', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('levels.0.abbr', 'CC')
                ->where('tiers.platoon.title', 'Company Commander')
                ->where('tiers.platoon.abbr', 'CC')
                ->where('tiers.squad.abbr', 'SL'));
    }

    #[Test]
    public function it_includes_one_two_and_four_level_examples(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;

        $this->actingAs($officer)
            ->get(route('division.leader-powers', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('examples', 3)
                ->where('examples.0.levels.0.tier', 'platoon')
                ->where('examples.1.levels.1.tier', 'squad')
                ->where('examples.2.levels.2.tier', 'platoon')
                ->where('examples.2.levels.3.tier', 'squad'));
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $division = $this->createActiveDivision();

        $this->get(route('division.leader-powers', $division->slug))->assertRedirect();
    }
}
