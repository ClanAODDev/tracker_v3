<?php

namespace Tests\Unit\Models;

use App\Models\Award;
use App\Models\MemberAward;
use App\Rules\UniqueAwardForMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AwardTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function get_rarity_returns_mythic_for_one_recipient()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('mythic', $award->getRarity(1));
    }

    #[Test]
    public function get_rarity_returns_legendary_for_few_recipients()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('legendary', $award->getRarity(10));
    }

    #[Test]
    public function get_rarity_returns_epic_for_moderate_recipients()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('epic', $award->getRarity(25));
    }

    #[Test]
    public function get_rarity_returns_rare_for_many_recipients()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('rare', $award->getRarity(50));
    }

    #[Test]
    public function get_rarity_returns_common_for_lots_of_recipients()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertEquals('common', $award->getRarity(100));
    }

    #[Test]
    public function get_rarity_uses_recipients_count_attribute_when_available()
    {
        $division                = $this->createActiveDivision();
        $award                   = Award::factory()->create(['division_id' => $division->id]);
        $award->recipients_count = 1;

        $this->assertEquals('mythic', $award->getRarity());
    }

    #[Test]
    public function scope_active_returns_only_active_awards()
    {
        $division = $this->createActiveDivision();

        $activeAward = Award::factory()->create([
            'division_id' => $division->id,
            'active'      => true,
        ]);

        $inactiveAward = Award::factory()->inactive()->create([
            'division_id' => $division->id,
        ]);

        $results = Award::active()->get();

        $this->assertTrue($results->contains($activeAward));
        $this->assertFalse($results->contains($inactiveAward));
    }

    #[Test]
    public function recipients_returns_only_approved_awards()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member1->id,
            'award_id'  => $award->id,
        ]);

        MemberAward::factory()->pending()->create([
            'member_id' => $member2->id,
            'award_id'  => $award->id,
        ]);

        $recipients = $award->recipients;

        $this->assertCount(1, $recipients);
        $this->assertEquals($member1->id, $recipients->first()->member_id);
    }

    #[Test]
    public function unapproved_recipients_returns_only_pending_awards()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $member1 = $this->createMember(['division_id' => $division->id]);
        $member2 = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member1->id,
            'award_id'  => $award->id,
        ]);

        MemberAward::factory()->pending()->create([
            'member_id' => $member2->id,
            'award_id'  => $award->id,
        ]);

        $unapproved = $award->unapprovedRecipients;

        $this->assertCount(1, $unapproved);
        $this->assertEquals($member2->id, $unapproved->first()->member_id);
    }

    #[Test]
    public function division_relationship_returns_correct_division()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertTrue($award->division->is($division));
    }

    #[Test]
    public function deleting_award_soft_deletes_and_removes_recipients()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $memberAward = MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        $award->delete();

        $this->assertSoftDeleted($award);
        $this->assertDatabaseMissing('award_member', ['id' => $memberAward->id]);
    }

    #[Test]
    public function repeatable_attribute_defaults_to_false()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertFalse($award->repeatable);
    }

    #[Test]
    public function repeatable_attribute_can_be_set_to_true()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => true,
        ]);

        $this->assertTrue($award->repeatable);
    }

    #[Test]
    public function unique_award_rule_fails_for_non_repeatable_award_when_member_already_has_it()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => false,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        $validator = Validator::make(
            ['member_id' => $member->clan_id],
            ['member_id' => [new UniqueAwardForMember($award->id)]]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('member_id', $validator->errors()->toArray());
    }

    #[Test]
    public function unique_award_rule_passes_for_repeatable_award_when_member_already_has_it()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => true,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        $validator = Validator::make(
            ['member_id' => $member->clan_id],
            ['member_id' => [new UniqueAwardForMember($award->id)]]
        );

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function unique_award_rule_passes_for_new_member()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => false,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        $validator = Validator::make(
            ['member_id' => $member->clan_id],
            ['member_id' => [new UniqueAwardForMember($award->id)]]
        );

        $this->assertFalse($validator->fails());
    }

    #[Test]
    public function member_can_have_multiple_instances_of_repeatable_award()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => true,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $award->id,
        ]);

        $this->assertCount(2, MemberAward::where('member_id', $member->id)
            ->where('award_id', $award->id)
            ->get());
    }

    #[Test]
    public function is_part_of_tiered_group_returns_true_when_has_prerequisite()
    {
        $division = $this->createActiveDivision();
        $tier1    = Award::factory()->create(['division_id' => $division->id]);
        $tier2    = Award::factory()->create([
            'division_id'           => $division->id,
            'prerequisite_award_id' => $tier1->id,
        ]);

        $this->assertTrue($tier2->isPartOfTieredGroup());
    }

    #[Test]
    public function is_part_of_tiered_group_returns_true_when_has_dependents()
    {
        $division = $this->createActiveDivision();
        $tier1    = Award::factory()->create(['division_id' => $division->id]);
        Award::factory()->create([
            'division_id'           => $division->id,
            'prerequisite_award_id' => $tier1->id,
        ]);

        $this->assertTrue($tier1->fresh()->isPartOfTieredGroup());
    }

    #[Test]
    public function is_part_of_tiered_group_returns_false_for_standalone_award()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertFalse($award->isPartOfTieredGroup());
    }

    #[Test]
    public function get_prerequisite_chain_returns_all_prerequisites_in_order()
    {
        $division = $this->createActiveDivision();
        $tier1    = Award::factory()->create(['division_id' => $division->id, 'name' => 'Tier 1']);
        $tier2    = Award::factory()->create([
            'division_id'           => $division->id,
            'name'                  => 'Tier 2',
            'prerequisite_award_id' => $tier1->id,
        ]);
        $tier3 = Award::factory()->create([
            'division_id'           => $division->id,
            'name'                  => 'Tier 3',
            'prerequisite_award_id' => $tier2->id,
        ]);

        $chain = $tier3->getPrerequisiteChain();

        $this->assertCount(2, $chain);
        $this->assertEquals($tier2->id, $chain[0]->id);
        $this->assertEquals($tier1->id, $chain[1]->id);
    }

    #[Test]
    public function get_tiered_group_slug_returns_slug_from_base_tier_name()
    {
        $division = $this->createActiveDivision();
        $tier1    = Award::factory()->create([
            'division_id'       => $division->id,
            'name'              => 'Service Award',
            'display_order'     => 1,
            'tiered_group_name' => 'AOD Tenure',
        ]);
        $tier2 = Award::factory()->create([
            'division_id'           => $division->id,
            'name'                  => 'Service Award II',
            'display_order'         => 2,
            'prerequisite_award_id' => $tier1->id,
        ]);

        $this->assertEquals('aod-tenure', $tier2->getTieredGroupSlug());
    }

    #[Test]
    public function get_tiered_group_slug_returns_null_for_standalone_award()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $this->assertNull($award->getTieredGroupSlug());
    }

    #[Test]
    public function tiered_award_cannot_be_repeated_even_if_marked_repeatable()
    {
        $division = $this->createActiveDivision();
        $tier1    = Award::factory()->create([
            'division_id' => $division->id,
            'repeatable'  => true,
        ]);
        $tier2 = Award::factory()->create([
            'division_id'           => $division->id,
            'repeatable'            => true,
            'prerequisite_award_id' => $tier1->id,
        ]);
        $member = $this->createMember(['division_id' => $division->id]);

        MemberAward::factory()->approved()->create([
            'member_id' => $member->id,
            'award_id'  => $tier2->id,
        ]);

        $validator = Validator::make(
            ['member_id' => $member->clan_id],
            ['member_id' => [new UniqueAwardForMember($tier2->id)]]
        );

        $this->assertTrue($validator->fails());
    }

    #[Test]
    public function recipients_excludes_members_without_division()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $memberWithDivision    = $this->createMember(['division_id' => $division->id]);
        $memberWithoutDivision = $this->createMember(['division_id' => $division->id]);
        $memberWithoutDivision->update(['division_id' => 0]);

        MemberAward::factory()->approved()->create([
            'member_id' => $memberWithDivision->id,
            'award_id'  => $award->id,
        ]);

        MemberAward::factory()->approved()->create([
            'member_id' => $memberWithoutDivision->id,
            'award_id'  => $award->id,
        ]);

        $recipients = $award->recipients;

        $this->assertCount(1, $recipients);
        $this->assertEquals($memberWithDivision->id, $recipients->first()->member_id);
    }

    #[Test]
    public function recipients_count_excludes_members_without_division()
    {
        $division = $this->createActiveDivision();
        $award    = Award::factory()->create(['division_id' => $division->id]);

        $memberWithDivision    = $this->createMember(['division_id' => $division->id]);
        $memberWithoutDivision = $this->createMember(['division_id' => $division->id]);
        $memberWithoutDivision->update(['division_id' => 0]);

        MemberAward::factory()->approved()->create([
            'member_id' => $memberWithDivision->id,
            'award_id'  => $award->id,
        ]);

        MemberAward::factory()->approved()->create([
            'member_id' => $memberWithoutDivision->id,
            'award_id'  => $award->id,
        ]);

        $count = Award::withCount('recipients')->find($award->id)->recipients_count;

        $this->assertEquals(1, $count);
    }
}
