<?php

namespace Tests\Feature\Controllers;

use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberDivisionDisplayTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function primary_division_is_shown()
    {
        $user   = $this->createMemberWithUser();
        $member = $user->member;

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('member/show')
                ->where('divisions.current.name', $member->division->name));
    }

    #[Test]
    public function part_time_divisions_are_shown()
    {
        $user     = $this->createMemberWithUser();
        $member   = $user->member;
        $partTime = $this->createActiveDivision();

        $member->partTimeDivisions()->attach($partTime->id);

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('divisions.partTime', fn ($partTimeDivs) => collect($partTimeDivs)->contains('name', $partTime->name)));
    }

    #[Test]
    public function part_time_list_is_empty_when_none_assigned()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->get(route('member', $user->member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('divisions.partTime', []));
    }

    #[Test]
    public function past_divisions_are_shown_from_transfer_history()
    {
        $user    = $this->createMemberWithUser();
        $member  = $user->member;
        $pastDiv = $this->createActiveDivision();

        Transfer::factory()->create([
            'member_id'   => $member->id,
            'division_id' => $pastDiv->id,
            'created_at'  => now()->subYear(),
        ]);
        Transfer::factory()->create([
            'member_id'   => $member->id,
            'division_id' => $member->division_id,
            'created_at'  => now()->subMonths(6),
        ]);

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('divisions.past', fn ($past) => collect($past)->contains('name', $pastDiv->name)));
    }

    #[Test]
    public function past_section_is_empty_with_no_transfer_history()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->get(route('member', $user->member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('divisions.past', []));
    }

    #[Test]
    public function repeated_past_division_visits_are_grouped_with_count()
    {
        $user         = $this->createMemberWithUser();
        $member       = $user->member;
        $returningDiv = $this->createActiveDivision();

        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $returningDiv->id, 'created_at' => now()->subYears(3)]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $member->division_id, 'created_at' => now()->subYears(2)]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $returningDiv->id, 'created_at' => now()->subYear()]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $member->division_id, 'created_at' => now()->subMonths(6)]);

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($returningDiv) {
                $entry = collect($page->toArray()['props']['divisions']['past'])
                    ->firstWhere('name', $returningDiv->name);

                $this->assertNotNull($entry);
                $this->assertSame(2, $entry['visits']);
            });
    }

    #[Test]
    public function current_division_is_excluded_from_past_section()
    {
        $user    = $this->createMemberWithUser();
        $member  = $user->member;
        $pastDiv = $this->createActiveDivision();

        Transfer::factory()->create([
            'member_id'   => $member->id,
            'division_id' => $pastDiv->id,
            'created_at'  => now()->subYear(),
        ]);
        Transfer::factory()->create([
            'member_id'   => $member->id,
            'division_id' => $member->division_id,
            'created_at'  => now()->subMonths(3),
        ]);

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('divisions.past', fn ($past) => ! collect($past)->contains('name', $member->division->name)));
    }
}
