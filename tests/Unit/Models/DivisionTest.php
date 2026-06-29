<?php

namespace Tests\Unit\Models;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function it_has_a_lowercase_abbreviation()
    {
        $division = Division::factory(['abbreviation' => 'UPPERCASE'])->make();

        $this->assertSame($division->abbreviation, 'uppercase');
    }

    #[Test]
    public function creating_division_sets_default_settings()
    {
        $division = Division::factory()->create(['name' => 'Test Division']);

        $this->assertIsArray($division->settings);
        $this->assertArrayHasKey('inactivity_days', $division->settings);
        $this->assertArrayHasKey('chat_alerts', $division->settings);
    }

    #[Test]
    public function creating_division_generates_slug_from_name()
    {
        $division = Division::factory()->create(['name' => 'Test Division Name']);

        $this->assertEquals('test-division-name', $division->slug);
    }

    #[Test]
    public function scope_active_returns_only_active_divisions()
    {
        $activeDivision   = $this->createActiveDivision();
        $inactiveDivision = $this->createInactiveDivision();

        $results = Division::active()->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertFalse($results->contains($inactiveDivision));
    }

    #[Test]
    public function scope_without_floaters_excludes_floater_division()
    {
        $regularDivision = Division::factory()->create(['name' => 'Regular', 'slug' => 'regular']);
        $floaterDivision = Division::factory()->create(['name' => 'Floater', 'slug' => 'floater']);

        $results = Division::withoutFloaters()->get();

        $this->assertTrue($results->contains($regularDivision));
        $this->assertFalse($results->contains($floaterDivision));
    }

    #[Test]
    public function scope_shutting_down_excludes_shutdown_divisions_by_default()
    {
        $activeDivision       = Division::factory()->create(['shutdown_at' => null]);
        $shuttingDownDivision = Division::factory()->create(['shutdown_at' => now()]);

        $results = Division::shuttingDown()->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertFalse($results->contains($shuttingDownDivision));
    }

    #[Test]
    public function scope_shutting_down_includes_shutdown_divisions_when_flag_set()
    {
        $activeDivision       = Division::factory()->create(['shutdown_at' => null]);
        $shuttingDownDivision = Division::factory()->create(['shutdown_at' => now()]);

        $results = Division::shuttingDown(true)->get();

        $this->assertTrue($results->contains($activeDivision));
        $this->assertTrue($results->contains($shuttingDownDivision));
    }

    #[Test]
    public function members_active_since_days_ago_filters_correctly()
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

    #[Test]
    public function members_active_on_discord_since_days_ago_filters_correctly()
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

    #[Test]
    public function sergeants_returns_members_with_rank_sergeant_or_higher()
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

    #[Test]
    public function sgt_and_ssgt_returns_only_sgt_and_staff_sgt()
    {
        $division = $this->createActiveDivision();

        $sgt = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::SERGEANT,
        ]);

        $ssgt = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);

        $ssg2 = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);

        $results = $division->sgtAndSsgt()->get();

        $this->assertTrue($results->contains($sgt));
        $this->assertTrue($results->contains($ssgt));
        $this->assertFalse($results->contains($ssg2));
    }

    #[Test]
    public function general_sergeants_returns_clan_admin_position_members()
    {
        $division = $this->createActiveDivision();

        $clanAdmin = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::CLAN_ADMIN,
        ]);

        $regularMember = $this->createMember([
            'division_id' => $division->id,
            'position'    => Position::MEMBER,
        ]);

        $results = $division->generalSergeants()->get();

        $this->assertTrue($results->contains($clanAdmin));
        $this->assertFalse($results->contains($regularMember));
    }

    #[Test]
    public function leaders_returns_co_and_xo()
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

    #[Test]
    public function unassigned_returns_members_without_platoon()
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

    #[Test]
    public function is_shutdown_returns_false_when_not_shut_down()
    {
        $division = Division::factory()->create(['shutdown_at' => null]);

        $this->assertFalse($division->isShutdown());
    }

    #[Test]
    public function is_shutdown_returns_true_when_shut_down()
    {
        $division = Division::factory()->create(['shutdown_at' => now()]);

        $this->assertTrue($division->isShutdown());
    }

    #[Test]
    public function route_notification_for_members_returns_configured_channel()
    {
        $division           = Division::factory()->create(['abbreviation' => 'arc']);
        $division->settings = array_merge($division->settings, ['member_channel' => 'arc-general']);
        $division->save();

        $this->assertEquals('arc-general', $division->routeNotificationForMembers());
    }

    #[Test]
    public function route_notification_for_members_falls_back_to_abbreviation()
    {
        $division           = Division::factory()->create(['abbreviation' => 'arc']);
        $division->settings = array_merge($division->settings, ['member_channel' => '']);
        $division->save();

        $this->assertEquals('arc-members', $division->routeNotificationForMembers());
    }

    #[Test]
    public function route_notification_for_officers_returns_configured_channel()
    {
        $division           = Division::factory()->create(['abbreviation' => 'arc']);
        $division->settings = array_merge($division->settings, ['officer_channel' => 'arc-staff']);
        $division->save();

        $this->assertEquals('arc-staff', $division->routeNotificationForOfficers());
    }

    #[Test]
    public function route_notification_for_officers_falls_back_to_abbreviation()
    {
        $division           = Division::factory()->create(['abbreviation' => 'arc']);
        $division->settings = array_merge($division->settings, ['officer_channel' => '']);
        $division->save();

        $this->assertEquals('arc-officers', $division->routeNotificationForOfficers());
    }

    #[Test]
    public function locality_returns_correct_translation()
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

    #[Test]
    public function locality_returns_ucwords_for_missing_translation()
    {
        $division = $this->createActiveDivision();

        $this->assertEquals('Unknown Term', $division->locality('unknown term'));
    }
}
