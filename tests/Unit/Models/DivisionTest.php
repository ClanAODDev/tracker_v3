<?php

namespace Tests\Unit\Models;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_it_has_a_lowercase_abbreviation()
    {
        $division = Division::factory(['abbreviation' => 'UPPERCASE'])->make();

        $this->assertSame($division->abbreviation, 'uppercase');
    }

    public function test_creating_division_sets_default_settings()
    {
        $division = Division::factory()->create(['name' => 'Test Division']);

        $this->assertIsArray($division->settings);
        $this->assertArrayHasKey('inactivity_days', $division->settings);
        $this->assertArrayHasKey('chat_alerts', $division->settings);
    }

    public function test_creating_division_generates_slug_from_name()
    {
        $division = Division::factory()->create(['name' => 'Test Division Name']);

        $this->assertEquals('test-division-name', $division->slug);
    }

    public function test_scope_active_returns_only_active_divisions()
    {
        $activeDivision   = $this->createActiveDivision();
        $inactiveDivision = $this->createInactiveDivision();

        $results = Division::active()->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertFalse($results->contains($inactiveDivision));
    }

    public function test_scope_without_floaters_excludes_floater_division()
    {
        $regularDivision = Division::factory()->create(['name' => 'Regular', 'slug' => 'regular']);
        $floaterDivision = Division::factory()->create(['name' => 'Floater', 'slug' => 'floater']);

        $results = Division::withoutFloaters()->get();

        $this->assertTrue($results->contains($regularDivision));
        $this->assertFalse($results->contains($floaterDivision));
    }

    public function test_scope_shutting_down_excludes_shutdown_divisions_by_default()
    {
        $activeDivision       = Division::factory()->create(['shutdown_at' => null]);
        $shuttingDownDivision = Division::factory()->create(['shutdown_at' => now()]);

        $results = Division::shuttingDown()->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertFalse($results->contains($shuttingDownDivision));
    }

    public function test_scope_shutting_down_includes_shutdown_divisions_when_flag_set()
    {
        $activeDivision       = Division::factory()->create(['shutdown_at' => null]);
        $shuttingDownDivision = Division::factory()->create(['shutdown_at' => now()]);

        $results = Division::shuttingDown(true)->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertTrue($results->contains($shuttingDownDivision));
    }

    public function test_members_active_since_days_ago_filters_correctly()
    {
        $division = $this->createActiveDivision();

        $activeRecently = $this->createMember([
            'division_id'   => $division->id,
            'last_activity' => Carbon::now()->subDays(5),
        ]);

        $inactiveOld = $this->createMember([
            'division_id'   => $division->id,
            'last_activity' => Carbon::now()->subDays(45),
        ]);

        $results = $division->membersActiveSinceDaysAgo(30)->get();

        $this->assertTrue($results->contains($activeRecently));
        $this->assertFalse($results->contains($inactiveOld));
    }

    public function test_members_active_on_ts_since_days_ago_filters_correctly()
    {
        $division = $this->createActiveDivision();

        $activeRecently = $this->createMember([
            'division_id'      => $division->id,
            'last_ts_activity' => Carbon::now()->subDays(5),
        ]);

        $inactiveOld = $this->createMember([
            'division_id'      => $division->id,
            'last_ts_activity' => Carbon::now()->subDays(45),
        ]);

        $results = $division->membersActiveOnTsSinceDaysAgo(30)->get();

        $this->assertTrue($results->contains($activeRecently));
        $this->assertFalse($results->contains($inactiveOld));
    }

    public function test_members_active_on_discord_since_days_ago_filters_correctly()
    {
        $division = $this->createActiveDivision();

        $activeRecently = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => Carbon::now()->subDays(5),
        ]);

        $inactiveOld = $this->createMember([
            'division_id'         => $division->id,
            'last_voice_activity' => Carbon::now()->subDays(45),
        ]);

        $results = $division->membersActiveOnDiscordSinceDaysAgo(30)->get();

        $this->assertTrue($results->contains($activeRecently));
        $this->assertFalse($results->contains($inactiveOld));
    }

    public function test_sergeants_returns_members_with_rank_sergeant_or_higher()
    {
        $division = $this->createActiveDivision();

        $sergeant = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::SERGEANT,
        ]);

        $corporal = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::CORPORAL,
        ]);

        $results = $division->sergeants()->get();

        $this->assertTrue($results->contains($sergeant));
        $this->assertFalse($results->contains($corporal));
    }

    public function test_leaders_returns_co_and_xo()
    {
        $division = $this->createActiveDivision();

        $co            = $this->createCommander($division);
        $xo            = $this->createExecutiveOfficer($division);
        $regularMember = $this->createMember(['division_id' => $division->id]);

        $results = $division->leaders()->get();

        $this->assertTrue($results->contains($co));
        $this->assertTrue($results->contains($xo));
        $this->assertFalse($results->contains($regularMember));
    }

    public function test_unassigned_returns_members_without_platoon()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $unassigned = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => 0,
            'position'    => Position::MEMBER,
        ]);

        $assigned = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::MEMBER,
        ]);

        $results = $division->unassigned()->get();

        $this->assertTrue($results->contains($unassigned));
        $this->assertFalse($results->contains($assigned));
    }

    public function test_is_active_returns_correct_value()
    {
        $activeDivision   = $this->createActiveDivision();
        $inactiveDivision = $this->createInactiveDivision();

        $this->assertTrue($activeDivision->isActive());
        $this->assertFalse($inactiveDivision->isActive());
    }

    public function test_is_shutdown_returns_correct_value()
    {
        $normalDivision   = Division::factory()->create(['shutdown_at' => null]);
        $shutdownDivision = Division::factory()->create(['shutdown_at' => now()]);

        $this->assertFalse((bool) $normalDivision->isShutdown());
        $this->assertTrue((bool) $shutdownDivision->isShutdown());
    }

    public function test_locality_returns_correct_translation()
    {
        $division           = $this->createActiveDivision();
        $division->settings = array_merge($division->defaultSettings, [
            'locality' => [
                ['old-string' => 'squad', 'new-string' => 'team'],
                ['old-string' => 'platoon', 'new-string' => 'company'],
            ],
        ]);
        $division->save();

        $this->assertEquals('Team', $division->locality('squad'));
        $this->assertEquals('Company', $division->locality('platoon'));
    }

    public function test_locality_returns_ucwords_for_missing_translation()
    {
        $division = $this->createActiveDivision();

        $this->assertEquals('Unknown Term', $division->locality('unknown term'));
    }

    public function test_new_members_last_30_returns_recent_joins()
    {
        $division = $this->createActiveDivision();

        $recentMember = $this->createMember([
            'division_id' => $division->id,
            'join_date'   => Carbon::now()->subDays(10),
        ]);

        $oldMember = $this->createMember([
            'division_id' => $division->id,
            'join_date'   => Carbon::now()->subDays(45),
        ]);

        $results = $division->newMembersLast30()->get();

        $this->assertTrue($results->contains($recentMember));
        $this->assertFalse($results->contains($oldMember));
    }
}
