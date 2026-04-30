<?php

namespace Tests\Feature\Controllers;

use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberDivisionDisplayTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_primary_division_card_is_shown()
    {
        $user   = $this->createMemberWithUser();
        $member = $user->member;

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertSee($member->division->name)
            ->assertSee('division-card--primary', false)
            ->assertSee('Primary');
    }

    public function test_part_time_divisions_are_shown()
    {
        $user      = $this->createMemberWithUser();
        $member    = $user->member;
        $partTime  = $this->createActiveDivision();

        $member->partTimeDivisions()->attach($partTime->id);

        $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->assertSee($partTime->name)
            ->assertSee('Part-Time');
    }

    public function test_part_time_badge_is_absent_when_none_assigned()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->get(route('member', $user->member->getUrlParams()))
            ->assertOk()
            ->assertDontSee('division-card-badge--secondary', false);
    }

    public function test_past_divisions_are_shown_from_transfer_history()
    {
        $user       = $this->createMemberWithUser();
        $member     = $user->member;
        $pastDiv    = $this->createActiveDivision();

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
            ->assertSee($pastDiv->name)
            ->assertSee('division-chip', false);
    }

    public function test_past_section_is_absent_with_no_transfer_history()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->get(route('member', $user->member->getUrlParams()))
            ->assertOk()
            ->assertDontSee('division-chip', false);
    }

    public function test_repeated_past_division_visits_are_grouped_with_count_badge()
    {
        $user         = $this->createMemberWithUser();
        $member       = $user->member;
        $returningDiv = $this->createActiveDivision();

        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $returningDiv->id, 'created_at' => now()->subYears(3)]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $member->division_id, 'created_at' => now()->subYears(2)]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $returningDiv->id, 'created_at' => now()->subYear()]);
        Transfer::factory()->create(['member_id' => $member->id, 'division_id' => $member->division_id, 'created_at' => now()->subMonths(6)]);

        $response = $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk();

        $response->assertSee('×2');
        $response->assertSee($returningDiv->name);
    }

    public function test_current_division_is_excluded_from_past_section()
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

        $content = $this->actingAs($user)
            ->get(route('member', $member->getUrlParams()))
            ->assertOk()
            ->getContent();

        $primaryCardCount = substr_count($content, 'division-card--primary');
        $this->assertSame(1, $primaryCardCount, 'Current division should appear once in primary card only');
    }
}
