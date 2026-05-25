<?php

namespace Tests\Unit\Models;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\DivisionTag;
use App\Models\Member;
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
    public function get_discord_url_returns_false_when_no_discord_id()
    {
        $member = $this->createMember(['discord_id' => null]);

        $this->assertFalse($member->getDiscordUrl());
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
}
