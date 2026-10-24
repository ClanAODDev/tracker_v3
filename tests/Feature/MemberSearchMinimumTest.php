<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberSearchMinimumTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function names(string $query): array
    {
        $response = $this->getJson(route('memberSearch', ['q' => $query]))->assertOk();

        return collect($response->json('results'))->pluck('rankName')
            ->map(fn (string $label) => collect(explode(' ', trim($label)))->last())
            ->all();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $division = $this->createActiveDivision();
        $this->actingAs($this->createOfficer($division));

        $this->createMember(['division_id' => $division->id, 'name' => 'DW']);
        $this->createMember(['division_id' => $division->id, 'name' => 'Joel', 'discord' => 'joel_gaming']);
        $this->createMember(['division_id' => $division->id, 'name' => 'Jonas']);
        $this->createMember(['division_id' => $division->id, 'name' => 'Alexander', 'discord' => 'ax']);
    }

    #[Test]
    public function a_query_at_the_minimum_matches_part_of_a_name(): void
    {
        $this->assertSame(['Joel', 'Jonas'], collect($this->names('Jo'))->sort()->values()->all());
        $this->assertSame(['Joel'], $this->names('oel'));
    }

    #[Test]
    public function a_short_query_is_an_exact_match_only(): void
    {
        $this->assertSame([], $this->names('J'));
        $this->assertSame([], $this->names('o'));
    }

    #[Test]
    public function members_with_short_names_can_still_be_found_by_their_full_name(): void
    {
        $this->assertSame(['DW'], $this->names('DW'));
        $this->assertSame(['DW'], $this->names('dw'));
    }

    #[Test]
    public function discord_names_are_matched_at_the_minimum_length(): void
    {
        $this->assertSame([], $this->names('x'));
        $this->assertSame(['Alexander'], $this->names('ax'));
    }

    #[Test]
    public function the_search_page_says_when_only_exact_matches_were_used(): void
    {
        $this->get(route('memberSearch', ['q' => 'D']))
            ->assertInertia(fn ($page) => $page->component('member/search')->where('partial', false)->where('query', 'D'));

        $this->get(route('memberSearch', ['q' => 'Joel']))
            ->assertInertia(fn ($page) => $page->where('partial', true));
    }

    #[Test]
    public function the_minimum_comes_from_configuration(): void
    {
        config(['aod.member_search.min_chars' => 3]);

        $this->assertSame([], $this->names('Jo'));
        $this->assertSame(['Jonas'], $this->names('Jon'));
    }

    #[Test]
    public function an_empty_query_returns_nothing_and_an_oversized_query_is_rejected(): void
    {
        $this->assertSame([], $this->names(''));
        $this->getJson(route('memberSearch', ['q' => str_repeat('a', 101)]))->assertUnprocessable();
    }

    #[Test]
    public function the_results_page_is_paginated(): void
    {
        $division = $this->createActiveDivision();
        foreach (range(1, 30) as $n) {
            $this->createMember(['division_id' => $division->id, 'name' => sprintf('Zed%02d', $n)]);
        }

        $this->get(route('memberSearch', ['q' => 'Zed']))
            ->assertInertia(fn ($page) => $page
                ->where('pagination.total', 30)
                ->where('pagination.lastPage', 2)
                ->has('results', 25));

        $this->get(route('memberSearch', ['q' => 'Zed', 'page' => 2]))
            ->assertInertia(fn ($page) => $page->has('results', 5)->where('pagination.currentPage', 2));
    }

    #[Test]
    public function the_live_search_response_is_capped(): void
    {
        $division = $this->createActiveDivision();
        foreach (range(1, 40) as $n) {
            $this->createMember(['division_id' => $division->id, 'name' => sprintf('Yak%02d', $n)]);
        }

        $this->assertCount(25, $this->names('Yak'));
    }
}
