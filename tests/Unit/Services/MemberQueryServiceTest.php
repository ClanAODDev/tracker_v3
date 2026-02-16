<?php

namespace Tests\Unit\Services;

use App\Enums\Rank;
use App\Models\Handle;
use App\Models\Member;
use App\Services\MemberQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberQueryServiceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private MemberQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MemberQueryService;
    }

    public function test_with_standard_relations_includes_handles()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $member->handles()->attach($division->handle_id, ['value' => 'testhandle', 'primary' => true]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('handles'));
    }

    public function test_with_standard_relations_includes_leave()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('leave'));
    }

    public function test_with_standard_relations_includes_tags()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('tags'));
    }

    public function test_with_standard_relations_includes_platoon()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
        ]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('platoon'));
    }

    public function test_with_standard_relations_includes_squad()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'platoon_id'  => $platoon->id,
            'squad_id'    => $squad->id,
        ]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('squad'));
    }

    public function test_primary_handle_constraint_filters_to_division_handle()
    {
        $division    = $this->createActiveDivision();
        $otherHandle = Handle::factory()->create();
        $member      = $this->createMember(['division_id' => $division->id]);

        $member->handles()->attach($division->handle_id, ['value' => 'primary_handle', 'primary' => true]);
        $member->handles()->attach($otherHandle->id, ['value' => 'other_handle', 'primary' => false]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertCount(1, $result->handles);
        $this->assertEquals('primary_handle', $result->handles->first()->pivot->value);
    }

    public function test_extract_handles_sets_handle_attribute_on_members()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $member->handles()->attach($division->handle_id, ['value' => 'testhandle', 'primary' => true]);

        $query   = Member::where('id', $member->id);
        $members = $this->service->withStandardRelations($query, $division)->get();
        $result  = $this->service->extractHandles($members);

        $this->assertNotNull($result->first()->handle);
        $this->assertEquals($division->handle_id, $result->first()->handle->id);
    }

    public function test_load_sorted_members_sorts_by_rank_descending()
    {
        $division = $this->createActiveDivision();

        $corporal = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::CORPORAL,
        ]);

        $sergeant = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::SERGEANT,
        ]);

        $private = $this->createMember([
            'division_id' => $division->id,
            'rank'        => Rank::PRIVATE_FIRST_CLASS,
        ]);

        $result = $this->service->loadSortedMembers($division->members(), $division);

        $this->assertEquals($sergeant->id, $result->first()->id);
        $this->assertEquals($private->id, $result->last()->id);
    }

    public function test_load_sorted_members_extracts_handles()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $member->handles()->attach($division->handle_id, ['value' => 'testhandle', 'primary' => true]);

        $result = $this->service->loadSortedMembers($division->members(), $division);

        $this->assertNotNull($result->first()->handle);
    }
}
