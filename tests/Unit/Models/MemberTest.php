<?php

namespace Tests\Unit\Models;

use App\Enums\DiscordStatus;
use App\Enums\Position;
use App\Enums\Rank;
use App\Models\DivisionTag;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function is_squad_leader_returns_true_when_member_is_squad_leader()
    {
        $squad  = $this->createSquad();
        $member = $this->createSquadLeader($squad);

        $this->assertTrue($member->isSquadLeader($squad));
    }

    #[Test]
    public function is_squad_leader_returns_false_when_member_is_not_squad_leader()
    {
        $squad  = $this->createSquad();
        $member = $this->createMember(['squad_id' => $squad->id]);

        $this->assertFalse($member->isSquadLeader($squad));
    }

    #[Test]
    public function is_platoon_leader_returns_true_when_member_is_platoon_leader()
    {
        $platoon = $this->createPlatoon();
        $member  = $this->createPlatoonLeader($platoon);

        $this->assertTrue($member->isPlatoonLeader($platoon));
    }

    #[Test]
    public function is_platoon_leader_returns_false_when_member_is_not_platoon_leader()
    {
        $platoon = $this->createPlatoon();
        $member  = $this->createMember(['platoon_id' => $platoon->id]);

        $this->assertFalse($member->isPlatoonLeader($platoon));
    }

    #[Test]
    public function is_division_leader_returns_true_for_commanding_officer()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createCommander($division);

        $this->assertTrue($member->isDivisionLeader($division));
    }

    #[Test]
    public function is_division_leader_returns_true_for_executive_officer()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createExecutiveOfficer($division);

        $this->assertTrue($member->isDivisionLeader($division));
    }

    #[Test]
    public function is_division_leader_returns_false_for_regular_member()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->assertFalse($member->isDivisionLeader($division));
    }

    #[Test]
    public function is_division_leader_returns_false_when_member_in_different_division()
    {
        $division1 = $this->createActiveDivision();
        $division2 = $this->createActiveDivision();
        $member    = $this->createCommander($division1);

        $this->assertFalse($member->isDivisionLeader($division2));
    }

    #[Test]
    public function reset_clears_division_assignments()
    {
        $division = $this->createDivisionWithFullStructure(1, 1, 1);
        $platoon  = $division->platoons->first();
        $squad    = $platoon->squads->first();

        $member = $this->createMember([
            'division_id'            => $division->id,
            'platoon_id'             => $platoon->id,
            'squad_id'               => $squad->id,
            'position'               => Position::SQUAD_LEADER,
            'flagged_for_inactivity' => true,
        ]);

        $member->reset();
        $member->refresh();

        $this->assertEquals(0, $member->division_id);
        $this->assertEquals(0, $member->platoon_id);
        $this->assertEquals(0, $member->squad_id);
        $this->assertEquals(Position::MEMBER, $member->position);
        $this->assertFalse($member->flagged_for_inactivity);
    }

    #[Test]
    public function reset_detaches_part_time_divisions()
    {
        $division         = $this->createActiveDivision();
        $partTimeDivision = $this->createActiveDivision();

        $member = $this->createMember(['division_id' => $division->id]);
        $member->partTimeDivisions()->attach($partTimeDivision->id);

        $this->assertCount(1, $member->partTimeDivisions);

        $member->reset();
        $member->refresh();

        $this->assertCount(0, $member->partTimeDivisions);
    }

    #[Test]
    public function reset_detaches_division_specific_tags()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $divisionTag = DivisionTag::factory()->create(['division_id' => $division->id]);
        $globalTag   = DivisionTag::factory()->global()->create();

        $member->tags()->attach([$divisionTag->id, $globalTag->id]);

        $this->assertCount(2, $member->tags);

        $member->reset();
        $member->refresh();

        $this->assertCount(1, $member->tags);
        $this->assertTrue($member->tags->contains($globalTag));
        $this->assertFalse($member->tags->contains($divisionTag));
    }

    #[Test]
    public function scope_unassigned_squad_leaders_returns_orphaned_leaders()
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));

        $assignedLeader = $this->createSquadLeader($squad);

        $unassignedLeader = Member::factory()->create([
            'division_id' => $division->id,
            'position'    => Position::SQUAD_LEADER,
        ]);

        $results = Member::unassignedSquadLeaders()->get();

        $this->assertTrue($results->contains($unassignedLeader));
        $this->assertFalse($results->contains($assignedLeader));
    }

    #[Test]
    public function scope_unassigned_platoon_leaders_returns_orphaned_leaders()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $assignedLeader = $this->createPlatoonLeader($platoon);

        $unassignedLeader = Member::factory()->create([
            'division_id' => $division->id,
            'position'    => Position::PLATOON_LEADER,
        ]);

        $results = Member::unassignedPlatoonLeaders()->get();

        $this->assertTrue($results->contains($unassignedLeader));
        $this->assertFalse($results->contains($assignedLeader));
    }

    #[Test]
    public function is_rank_with_single_rank_returns_correct_value()
    {
        $member = $this->createMember(['rank' => Rank::SERGEANT]);

        $this->assertTrue($member->isRank(Rank::SERGEANT));
        $this->assertFalse($member->isRank(Rank::CORPORAL));
    }

    #[Test]
    public function is_rank_with_array_returns_correct_value()
    {
        $member = $this->createMember(['rank' => Rank::SERGEANT]);

        $this->assertTrue($member->isRank([Rank::SERGEANT, Rank::STAFF_SERGEANT]));
        $this->assertFalse($member->isRank([Rank::CORPORAL, Rank::SPECIALIST]));
    }

    #[Test]
    public function bot_response_formats_correctly()
    {
        $division = $this->createActiveDivision(['name' => 'Test Division']);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::SERGEANT,
            'discord'     => 'testuser#1234',
        ]);

        $response = $member->botResponse();

        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('value', $response);
        $this->assertStringContainsString('Test Division', $response['name']);
        $this->assertStringContainsString('testuser#1234', $response['value']);
    }

    #[Test]
    public function bot_response_shows_ex_aod_for_member_without_division()
    {
        $member = $this->createMember(['division_id' => 0]);
        $member->load('division');

        $response = $member->botResponse();

        $this->assertStringContainsString('Ex-AOD', $response['name']);
    }

    #[Test]
    public function get_discord_url_returns_url_when_discord_id_set()
    {
        $member = $this->createMember(['discord_id' => '123456789']);

        $url = $member->getDiscordUrl();

        $this->assertEquals('https://discordapp.com/users/123456789', $url);
    }

    #[Test]
    public function get_discord_url_returns_null_when_no_discord_id()
    {
        $member = $this->createMember(['discord_id' => null]);

        $this->assertNull($member->getDiscordUrl());
    }

    #[Test]
    public function discord_id_is_always_returned_as_string_to_prevent_js_precision_loss()
    {
        $snowflake = '319196306516213763';

        $member = $this->createMember(['discord_id' => $snowflake]);
        $member->refresh();

        $this->assertIsString($member->discord_id);
        $this->assertSame($snowflake, $member->discord_id);
    }

    #[Test]
    public function get_discord_avatar_url_returns_null_when_no_discord_id()
    {
        $member = $this->createMember(['discord_id' => null]);

        $this->assertNull($member->getDiscordAvatarUrl());
    }

    #[Test]
    public function get_discord_avatar_url_returns_cdn_url_with_avatar_hash()
    {
        $member = $this->createMember([
            'discord_id'     => '123456789',
            'discord_avatar' => 'abc123hash',
        ]);

        $this->assertEquals(
            'https://cdn.discordapp.com/avatars/123456789/abc123hash.png?size=64',
            $member->getDiscordAvatarUrl()
        );
    }

    #[Test]
    public function get_discord_avatar_url_uses_modular_default_when_no_avatar_hash()
    {
        $discordId = '319196306516213763';
        $member    = $this->createMember([
            'discord_id'     => $discordId,
            'discord_avatar' => null,
        ]);

        $expected = sprintf(
            'https://cdn.discordapp.com/embed/avatars/%d.png',
            abs((int) $discordId >> 22) % 6
        );

        $this->assertEquals($expected, $member->getDiscordAvatarUrl());
    }

    #[Test]
    public function scope_misconfigured_discord_returns_members_with_invalid_statuses()
    {
        $neverConnected  = $this->createMember(['last_voice_status' => DiscordStatus::NEVER_CONNECTED]);
        $neverConfigured = $this->createMember(['last_voice_status' => DiscordStatus::NEVER_CONFIGURED]);
        $disconnected    = $this->createMember(['last_voice_status' => DiscordStatus::DISCONNECTED]);
        $connected       = $this->createMember(['last_voice_status' => DiscordStatus::CONNECTED]);

        $results = Member::misconfiguredDiscord()->get();

        $this->assertTrue($results->contains($neverConnected));
        $this->assertTrue($results->contains($neverConfigured));
        $this->assertTrue($results->contains($disconnected));
        $this->assertFalse($results->contains($connected));
    }

    #[Test]
    public function eligible_for_rank_action_member_is_scoped_to_own_squad_and_rank_limit()
    {
        $squad = $this->createSquad($this->createPlatoon($this->createActiveDivision()));

        $user = $this->createMemberWithUser([
            'squad_id' => $squad->id,
            'position' => Position::MEMBER,
            'rank'     => Rank::SERGEANT,
        ]);

        $eligible       = $this->createMember(['squad_id' => $squad->id, 'rank' => Rank::RECRUIT]);
        $rankTooHigh    = $this->createMember(['squad_id' => $squad->id, 'rank' => Rank::SPECIALIST]);
        $differentSquad = $this->createMember(['squad_id' => $this->createSquad()->id, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user)->get();

        $this->assertTrue($results->contains($eligible));
        $this->assertFalse($results->contains($rankTooHigh));
        $this->assertFalse($results->contains($differentSquad));
        $this->assertFalse($results->contains($user->member));
    }

    #[Test]
    public function eligible_for_rank_action_squad_leader_is_scoped_to_own_squad()
    {
        $squad = $this->createSquad($this->createPlatoon($this->createActiveDivision()));

        $user = $this->createMemberWithUser([
            'squad_id' => $squad->id,
            'position' => Position::SQUAD_LEADER,
            'rank'     => Rank::SERGEANT,
        ]);

        $eligible       = $this->createMember(['squad_id' => $squad->id, 'rank' => Rank::RECRUIT]);
        $differentSquad = $this->createMember(['squad_id' => $this->createSquad()->id, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user)->get();

        $this->assertTrue($results->contains($eligible));
        $this->assertFalse($results->contains($differentSquad));
    }

    #[Test]
    public function eligible_for_rank_action_platoon_leader_is_scoped_to_own_platoon_and_rank_limit()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);

        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'position'    => Position::PLATOON_LEADER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);

        $eligible          = $this->createMember(['platoon_id' => $platoon->id, 'rank' => Rank::RECRUIT]);
        $rankTooHigh       = $this->createMember(['platoon_id' => $platoon->id, 'rank' => Rank::CORPORAL]);
        $differentPlatoon  = $this->createMember(['platoon_id' => $this->createPlatoon($division)->id, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user)->get();

        $this->assertTrue($results->contains($eligible));
        $this->assertFalse($results->contains($rankTooHigh));
        $this->assertFalse($results->contains($differentPlatoon));
    }

    #[Test]
    public function eligible_for_rank_action_division_leader_is_scoped_to_own_division_and_rank_limit()
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();

        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);

        $eligible          = $this->createMember(['division_id' => $division->id, 'rank' => Rank::RECRUIT]);
        $rankTooHigh       = $this->createMember(['division_id' => $division->id, 'rank' => Rank::STAFF_SERGEANT]);
        $differentDivision = $this->createMember(['division_id' => $otherDivision->id, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user)->get();

        $this->assertTrue($results->contains($eligible));
        $this->assertFalse($results->contains($rankTooHigh));
        $this->assertFalse($results->contains($differentDivision));
    }

    #[Test]
    public function eligible_for_rank_action_admin_sees_all_members_with_a_division()
    {
        $user = $this->createAdmin();

        $withDivision    = $this->createMember(['division_id' => $this->createActiveDivision()->id, 'rank' => Rank::RECRUIT]);
        $withoutDivision = $this->createMember(['division_id' => 0, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user)->get();

        $this->assertTrue($results->contains($withDivision));
        $this->assertFalse($results->contains($withoutDivision));
    }

    #[Test]
    public function eligible_for_rank_action_search_filters_by_name()
    {
        $user = $this->createAdmin();

        $target = $this->createMember(['name' => 'uniqueUsername', 'division_id' => $this->createActiveDivision()->id, 'rank' => Rank::RECRUIT]);
        $other  = $this->createMember(['name' => 'someoneElse', 'division_id' => $this->createActiveDivision()->id, 'rank' => Rank::RECRUIT]);

        $results = Member::eligibleForRankAction($user, 'uniqueUser')->get();

        $this->assertTrue($results->contains($target));
        $this->assertFalse($results->contains($other));
    }

    #[Test]
    public function reset_clears_pending_transfers()
    {
        $member   = $this->createMember();
        $division = $this->createActiveDivision();

        $pending  = Transfer::factory()->pending()->create(['member_id' => $member->id, 'division_id' => $division->id]);
        $approved = Transfer::factory()->approved()->create(['member_id' => $member->id, 'division_id' => $division->id]);

        $member->reset();

        $this->assertDatabaseMissing('transfers', ['id' => $pending->id]);
        $this->assertDatabaseHas('transfers', ['id' => $approved->id]);
    }

    #[Test]
    public function reset_deletes_active_leave()
    {
        $member = $this->createMember();
        $leave  = Leave::factory()->create(['member_id' => $member->id]);

        $member->reset();

        $this->assertDatabaseMissing('leaves', ['id' => $leave->id]);
    }

    #[Test]
    public function deleting_member_removes_pending_transfers()
    {
        $member   = $this->createMember();
        $division = $this->createActiveDivision();

        $pending  = Transfer::factory()->pending()->create(['member_id' => $member->id, 'division_id' => $division->id]);
        $approved = Transfer::factory()->approved()->create(['member_id' => $member->id, 'division_id' => $division->id]);

        $member->delete();

        $this->assertDatabaseMissing('transfers', ['id' => $pending->id]);
        $this->assertDatabaseHas('transfers', ['id' => $approved->id]);
    }

    #[Test]
    public function expired_leave_returns_leave_with_past_end_date()
    {
        $member  = $this->createMember();
        $expired = Leave::factory()->expired()->create(['member_id' => $member->id]);

        $this->assertTrue($member->expiredLeave()->exists());
        $this->assertEquals($expired->id, $member->expiredLeave()->first()->id);
    }

    #[Test]
    public function active_leave_returns_leave_with_future_end_date()
    {
        $member = $this->createMember();
        Leave::factory()->create(['member_id' => $member->id]);

        $this->assertTrue($member->activeLeave()->exists());
    }

    #[Test]
    public function recruiter_relationship_resolves_via_clan_id()
    {
        $recruiter = $this->createMember();
        $recruit   = $this->createMember(['recruiter_id' => $recruiter->clan_id]);

        $this->assertEquals($recruiter->id, $recruit->recruiter->id);
    }

    #[Test]
    public function recruits_returns_members_recruited_by_this_member()
    {
        $recruiter = $this->createMember();
        $recruit   = $this->createMember(['recruiter_id' => $recruiter->clan_id]);
        $other     = $this->createMember();

        $recruits = $recruiter->recruits;

        $this->assertTrue($recruits->contains($recruit));
        $this->assertFalse($recruits->contains($other));
    }

    #[Test]
    public function aod_profile_link_builds_url_from_clan_id()
    {
        $member = $this->createMember(['clan_id' => 12345]);

        $this->assertEquals(
            'http://www.clanaod.net/forums/member.php?u=12345',
            $member->AODProfileLink
        );
    }

    #[Test]
    public function voice_invalid_returns_true_when_no_voice_activity()
    {
        $member = $this->createMember(['last_voice_activity' => null]);

        $this->assertTrue($member->voice_invalid);
    }

    #[Test]
    public function voice_invalid_returns_false_when_voice_activity_exists()
    {
        $member = $this->createMember(['last_voice_activity' => now()]);

        $this->assertFalse($member->voice_invalid);
    }

    #[Test]
    public function last_promoted_returns_formatted_date()
    {
        $member = $this->createMember(['last_promoted_at' => '2024-06-15 00:00:00']);

        $this->assertEquals('2024-06-15', $member->last_promoted);
    }

    #[Test]
    public function last_promoted_returns_never_when_not_set()
    {
        $member = $this->createMember(['last_promoted_at' => null]);

        $this->assertEquals('Never', $member->last_promoted);
    }

    #[Test]
    public function is_pending_returns_true_when_pending_member_request_exists()
    {
        $member = $this->createMember();
        MemberRequest::factory()->create(['member_id' => $member->id]);

        $this->assertTrue($member->is_pending);
    }

    #[Test]
    public function is_pending_returns_false_when_no_pending_request()
    {
        $member = $this->createMember();

        $this->assertFalse($member->is_pending);
    }

    #[Test]
    public function voice_status_formats_enum_value_as_title_case()
    {
        $member = $this->createMember(['last_voice_status' => DiscordStatus::NEVER_CONNECTED]);

        $this->assertEquals('Never Connected', (string) $member->voice_status);
    }
}
