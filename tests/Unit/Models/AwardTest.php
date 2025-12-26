<?php

namespace Tests\Unit\Models;

use App\Models\Award;
use App\Models\MemberAward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AwardTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_get_rarity_returns_mythic_for_one_recipient()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('mythic', $award->getRarity(1));
    }

    public function test_get_rarity_returns_legendary_for_few_recipients()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('legendary', $award->getRarity(10));
    }

    public function test_get_rarity_returns_epic_for_moderate_recipients()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('epic', $award->getRarity(25));
    }

    public function test_get_rarity_returns_rare_for_many_recipients()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('rare', $award->getRarity(50));
    }

    public function test_get_rarity_returns_common_for_lots_of_recipients()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('common', $award->getRarity(100));
    }

    public function test_get_rarity_uses_recipients_count_attribute_when_available()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);
        $award->recipients_count = 1;

        $this->assertEquals('mythic', $award->getRarity());
    }

    public function test_scope_active_returns_only_active_awards()
    {
        $division = $this->createActiveDivision();

        $activeAward = Award::factory()->create([
            'division_id' => $division->id,
            'active' => true,
        ]);

        $inactiveAward = Award::factory()->inactive()->create([
            'division_id' => $division->id,
        ]);

        $results = Award::active()->get();

        $this->assertTrue($results->contains($activeAward));
        $this->assertFalse($results->contains($inactiveAward));
    }

    public function test_recipients_returns_only_approved_awards()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member1->clan_id,
            'award_id' => $award->id,
        ]);

        MemberAward::factory()->pending()->create([
            'member_id' => $member2->clan_id,
            'award_id' => $award->id,
        ]);

        $recipients = $award->recipients;

        $this->assertCount(1, $recipients);
        $this->assertEquals($member1->clan_id, $recipients->first()->member_id);
    }

    public function test_unapproved_recipients_returns_only_pending_awards()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member1->clan_id,
            'award_id' => $award->id,
        ]);

        MemberAward::factory()->pending()->create([
            'member_id' => $member2->clan_id,
            'award_id' => $award->id,
        ]);

        $unapproved = $award->unapprovedRecipients;

        $this->assertCount(1, $unapproved);
        $this->assertEquals($member2->clan_id, $unapproved->first()->member_id);
    }

    public function test_division_relationship_returns_correct_division()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($award->division->is($division));
    }

    public function test_deleting_award_soft_deletes_and_removes_recipients()
    {
        $division = $this->createActiveDivision();
        $award = Award::factory()->create(['division_id' => $division->id]);
        $member = $this->createMember(['division_id' => $division->id]);

        $memberAward = MemberAward::factory()->approved()->create([
            'member_id' => $member->clan_id,
            'award_id' => $award->id,
        ]);

        $award->delete();

        $this->assertSoftDeleted($award);
        $this->assertDatabaseMissing('award_member', ['id' => $memberAward->id]);
    }
}
