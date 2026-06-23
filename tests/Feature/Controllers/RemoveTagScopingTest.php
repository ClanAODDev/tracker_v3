<?php

namespace Tests\Feature\Controllers;

use App\Models\DivisionTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RemoveTagScopingTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function officer_cannot_remove_tag_belonging_to_another_division()
    {
        $officer       = $this->createOfficer();
        $division      = $officer->member->division;
        $member        = $this->createMember(['division_id' => $division->id]);
        $otherDivision = $this->createActiveDivision();
        $foreignTag    = DivisionTag::factory()->create(['division_id' => $otherDivision->id]);
        $member->tags()->attach($foreignTag->id);

        $this->actingAs($officer)
            ->postJson(route('member-tags.remove', [$division->slug, $member->clan_id]), [
                'tag_id' => $foreignTag->id,
            ])->assertUnprocessable();

        $this->assertTrue($member->fresh()->tags->contains($foreignTag));
    }

    #[Test]
    public function officer_can_remove_tag_belonging_to_their_division()
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $tag      = DivisionTag::factory()->create(['division_id' => $division->id]);
        $member->tags()->attach($tag->id);

        $this->actingAs($officer)
            ->postJson(route('member-tags.remove', [$division->slug, $member->clan_id]), [
                'tag_id' => $tag->id,
            ])->assertOk();

        $this->assertFalse($member->fresh()->tags->contains($tag));
    }

    #[Test]
    public function officer_can_remove_global_tag()
    {
        $officer   = $this->createOfficer();
        $division  = $officer->member->division;
        $member    = $this->createMember(['division_id' => $division->id]);
        $globalTag = DivisionTag::factory()->global()->create();
        $member->tags()->attach($globalTag->id);

        $this->actingAs($officer)
            ->postJson(route('member-tags.remove', [$division->slug, $member->clan_id]), [
                'tag_id' => $globalTag->id,
            ])->assertOk();

        $this->assertFalse($member->fresh()->tags->contains($globalTag));
    }

    #[Test]
    public function officer_can_remove_own_division_tag_from_member_in_different_division()
    {
        $officer       = $this->createOfficer();
        $officerDiv    = $officer->member->division;
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember(['division_id' => $otherDivision->id]);
        $tag           = DivisionTag::factory()->create(['division_id' => $officerDiv->id]);
        $member->tags()->attach($tag->id);

        $this->actingAs($officer)
            ->postJson(route('member-tags.remove', [$otherDivision->slug, $member->clan_id]), [
                'tag_id' => $tag->id,
            ])->assertOk();

        $this->assertFalse($member->fresh()->tags->contains($tag));
    }
}
