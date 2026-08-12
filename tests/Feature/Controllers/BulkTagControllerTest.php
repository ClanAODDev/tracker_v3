<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\DivisionTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class BulkTagControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function bulk_store_assigns_tags_to_multiple_members()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member1  = $this->createMember(['division_id' => $division->id]);
        $member2  = $this->createMember(['division_id' => $division->id]);
        $tag      = DivisionTag::factory()->create(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->postJson(route('bulk-tags.store', $division->slug), [
                'member_ids' => [$member1->clan_id, $member2->clan_id],
                'tags'       => [$tag->id],
                'action'     => 'assign',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertTrue($member1->fresh()->tags->contains($tag));
        $this->assertTrue($member2->fresh()->tags->contains($tag));
    }

    #[Test]
    public function bulk_store_removes_tags_from_multiple_members()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member1  = $this->createMember(['division_id' => $division->id]);
        $member2  = $this->createMember(['division_id' => $division->id]);
        $tag      = DivisionTag::factory()->create(['division_id' => $division->id]);

        $member1->tags()->attach($tag->id);
        $member2->tags()->attach($tag->id);

        $response = $this->actingAs($srLdr)
            ->postJson(route('bulk-tags.store', $division->slug), [
                'member_ids' => [$member1->clan_id, $member2->clan_id],
                'tags'       => [$tag->id],
                'action'     => 'remove',
            ]);

        $response->assertOk();
        $this->assertFalse($member1->fresh()->tags->contains($tag));
        $this->assertFalse($member2->fresh()->tags->contains($tag));
    }

    #[Test]
    public function bulk_store_does_not_apply_tags_the_user_cannot_assign()
    {
        $officer       = $this->createOfficer(memberAttributes: ['rank' => Rank::CORPORAL]);
        $division      = $officer->member->division;
        $member        = $this->createMember(['division_id' => $division->id]);
        $otherDivision = $this->createActiveDivision();
        $foreignTag    = DivisionTag::factory()->create(['division_id' => $otherDivision->id]);

        $response = $this->actingAs($officer)
            ->postJson(route('bulk-tags.store', $division->slug), [
                'member_ids' => [$member->clan_id],
                'tags'       => [$foreignTag->id],
                'action'     => 'assign',
            ]);

        $response->assertOk();
        $this->assertFalse($member->fresh()->tags->contains($foreignTag));
    }

    #[Test]
    public function bulk_store_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->postJson(route('bulk-tags.store', $division->slug), [
            'member_ids' => [1],
            'tags'       => [1],
            'action'     => 'assign',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function create_new_tag_control_is_hidden_for_officer()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($officer)
            ->post(route('bulk-tags.create', $division->slug), [
                'member-data' => (string) $member->clan_id,
            ]);

        $response->assertOk();
        $response->assertDontSee('Create New Tag');
    }

    #[Test]
    public function create_new_tag_control_is_visible_for_sr_ldr()
    {
        $srLdr    = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('bulk-tags.create', $division->slug), [
                'member-data' => (string) $member->clan_id,
            ]);

        $response->assertOk();
        $response->assertSee('Create New Tag');
    }
}
