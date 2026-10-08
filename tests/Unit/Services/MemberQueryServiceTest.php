<?php

namespace Tests\Unit\Services;

use App\Enums\Rank;
use App\Models\Handle;
use App\Models\Member;
use App\Services\MemberQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function with_standard_relations_includes_handles()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $member->handles()->attach($division->handles->first()->id, ['value' => 'testhandle', 'primary' => true]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('handles'));
    }

    #[Test]
    public function with_standard_relations_includes_leave()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('leave'));
    }

    #[Test]
    public function with_standard_relations_includes_tags()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('tags'));
    }

    #[Test]
    public function with_standard_relations_includes_the_platoon_level_unit()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'unit_id'     => $platoon->id,
        ]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('unit'));
        $this->assertSame($platoon->id, $result->platoonUnit()->id);
    }

    #[Test]
    public function with_standard_relations_includes_the_squad_and_its_platoon()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'unit_id'     => $squad->id,
        ]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertTrue($result->relationLoaded('unit'));
        $this->assertTrue($result->unit->relationLoaded('parent'));
        $this->assertSame([$squad->id, $platoon->id], [$result->squadUnit()->id, $result->platoonUnit()->id]);
    }

    #[Test]
    public function division_handles_constraint_excludes_other_handle_types()
    {
        $division    = $this->createActiveDivision();
        $otherHandle = Handle::factory()->create();
        $member      = $this->createMember(['division_id' => $division->id]);

        $member->handles()->attach($division->handles->first()->id, ['value' => 'primary_handle', 'primary' => true]);
        $member->handles()->attach($otherHandle->id, ['value' => 'other_handle', 'primary' => false]);

        $query  = Member::where('id', $member->id);
        $result = $this->service->withStandardRelations($query, $division)->first();

        $this->assertCount(1, $result->handles);
        $this->assertEquals('primary_handle', $result->handles->first()->pivot->value);
    }

    #[Test]
    public function division_handles_constraint_includes_every_division_handle_type()
    {
        $na       = Handle::factory()->create();
        $eu       = Handle::factory()->create();
        $other    = Handle::factory()->create();
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $member->handles()->attach($na->id, ['value' => 'na_handle', 'primary' => true]);
        $member->handles()->attach($eu->id, ['value' => 'eu_handle', 'primary' => true]);
        $member->handles()->attach($other->id, ['value' => 'other_handle', 'primary' => true]);

        $result = $this->service->withStandardRelations(Member::where('id', $member->id), $division)->first();

        $this->assertEqualsCanonicalizing(['na_handle', 'eu_handle'], $result->handles->pluck('pivot.value')->all());
    }

    #[Test]
    public function load_sorted_members_sorts_by_rank_descending()
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

    #[Test]
    public function load_sorted_members_loads_division_handles()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);
        $member->handles()->attach($division->handles->first()->id, ['value' => 'testhandle', 'primary' => true]);

        $result = $this->service->loadSortedMembers($division->members(), $division);

        $this->assertSame('testhandle', $result->first()->handles->first()->pivot->value);
    }
}
