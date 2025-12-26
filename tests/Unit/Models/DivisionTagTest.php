<?php

namespace Tests\Unit\Models;

use App\Models\DivisionTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionTagTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_is_global_returns_true_for_null_division()
    {
        $tag = DivisionTag::factory()->global()->create();

        $this->assertTrue($tag->isGlobal());
    }

    public function test_is_global_returns_false_for_division_tag()
    {
        $division = $this->createActiveDivision();
        $tag = DivisionTag::factory()->create(['division_id' => $division->id]);

        $this->assertFalse($tag->isGlobal());
    }

    public function test_scope_global_returns_only_global_tags()
    {
        $division = $this->createActiveDivision();
        $globalTag = DivisionTag::factory()->global()->create();
        $divisionTag = DivisionTag::factory()->create(['division_id' => $division->id]);

        $results = DivisionTag::global()->get();

        $this->assertTrue($results->contains($globalTag));
        $this->assertFalse($results->contains($divisionTag));
    }

    public function test_scope_by_division_returns_only_that_divisions_tags()
    {
        $division1 = $this->createActiveDivision();
        $division2 = $this->createActiveDivision();

        $tag1 = DivisionTag::factory()->create(['division_id' => $division1->id]);
        $tag2 = DivisionTag::factory()->create(['division_id' => $division2->id]);

        $results = DivisionTag::byDivision($division1->id)->get();

        $this->assertTrue($results->contains($tag1));
        $this->assertFalse($results->contains($tag2));
    }

    public function test_scope_for_division_includes_global_tags()
    {
        $division = $this->createActiveDivision();

        $divisionTag = DivisionTag::factory()->create(['division_id' => $division->id]);
        $globalTag = DivisionTag::factory()->global()->create();

        $results = DivisionTag::forDivision($division->id)->get();

        $this->assertTrue($results->contains($divisionTag));
        $this->assertTrue($results->contains($globalTag));
    }

    public function test_scope_for_division_excludes_other_division_tags()
    {
        $division1 = $this->createActiveDivision();
        $division2 = $this->createActiveDivision();

        $tag1 = DivisionTag::factory()->create(['division_id' => $division1->id]);
        $tag2 = DivisionTag::factory()->create(['division_id' => $division2->id]);

        $results = DivisionTag::forDivision($division1->id)->get();

        $this->assertTrue($results->contains($tag1));
        $this->assertFalse($results->contains($tag2));
    }

    public function test_is_visible_to_returns_true_for_public_tag_without_user()
    {
        $tag = DivisionTag::factory()->public()->create();

        $this->assertTrue($tag->isVisibleTo(null));
    }

    public function test_is_visible_to_returns_false_for_officers_tag_without_user()
    {
        $tag = DivisionTag::factory()->officersOnly()->create();

        $this->assertTrue($tag->isVisibleTo(null) === false);
    }

    public function test_is_visible_to_returns_true_for_admin_on_any_tag()
    {
        $admin = $this->createAdmin();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $this->assertTrue($publicTag->isVisibleTo($admin));
        $this->assertTrue($officersTag->isVisibleTo($admin));
        $this->assertTrue($srLdrsTag->isVisibleTo($admin));
    }

    public function test_is_visible_to_returns_true_for_sr_ldr_on_any_tag()
    {
        $srLeader = $this->createSeniorLeader();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $this->assertTrue($publicTag->isVisibleTo($srLeader));
        $this->assertTrue($officersTag->isVisibleTo($srLeader));
        $this->assertTrue($srLdrsTag->isVisibleTo($srLeader));
    }

    public function test_is_visible_to_returns_correct_values_for_officer()
    {
        $officer = $this->createOfficer();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $this->assertTrue($publicTag->isVisibleTo($officer));
        $this->assertTrue($officersTag->isVisibleTo($officer));
        $this->assertFalse($srLdrsTag->isVisibleTo($officer));
    }

    public function test_scope_visible_to_filters_correctly_for_null_user()
    {
        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();

        $results = DivisionTag::visibleTo(null)->get();

        $this->assertTrue($results->contains($publicTag));
        $this->assertFalse($results->contains($officersTag));
    }

    public function test_scope_visible_to_returns_all_for_admin()
    {
        $admin = $this->createAdmin();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $results = DivisionTag::visibleTo($admin)->get();

        $this->assertTrue($results->contains($publicTag));
        $this->assertTrue($results->contains($officersTag));
        $this->assertTrue($results->contains($srLdrsTag));
    }

    public function test_scope_visible_to_filters_correctly_for_officer()
    {
        $officer = $this->createOfficer();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $results = DivisionTag::visibleTo($officer)->get();

        $this->assertTrue($results->contains($publicTag));
        $this->assertTrue($results->contains($officersTag));
        $this->assertFalse($results->contains($srLdrsTag));
    }

    public function test_scope_assignable_by_returns_none_for_null_user()
    {
        $publicTag = DivisionTag::factory()->public()->create();

        $results = DivisionTag::assignableBy(null)->get();

        $this->assertFalse($results->contains($publicTag));
    }

    public function test_scope_assignable_by_returns_all_for_admin()
    {
        $admin = $this->createAdmin();

        $publicTag = DivisionTag::factory()->public()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $results = DivisionTag::assignableBy($admin)->get();

        $this->assertTrue($results->contains($publicTag));
        $this->assertTrue($results->contains($srLdrsTag));
    }

    public function test_scope_assignable_by_filters_correctly_for_officer()
    {
        $officer = $this->createOfficer();

        $publicTag = DivisionTag::factory()->public()->create();
        $officersTag = DivisionTag::factory()->officersOnly()->create();
        $srLdrsTag = DivisionTag::factory()->seniorLeadersOnly()->create();

        $results = DivisionTag::assignableBy($officer)->get();

        $this->assertTrue($results->contains($publicTag));
        $this->assertTrue($results->contains($officersTag));
        $this->assertFalse($results->contains($srLdrsTag));
    }

    public function test_members_relationship_returns_tagged_members()
    {
        $division = $this->createActiveDivision();
        $tag = DivisionTag::factory()->create(['division_id' => $division->id]);
        $member = $this->createMember(['division_id' => $division->id]);

        $member->tags()->attach($tag->id);

        $this->assertTrue($tag->members->contains($member));
    }
}
