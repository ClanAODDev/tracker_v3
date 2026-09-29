<?php

namespace Tests\Unit\Models;

use App\Models\Handle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionHandlesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function handles_are_ordered_by_sort_order()
    {
        $eu       = Handle::factory()->create(['label' => 'Warships EU']);
        $na       = Handle::factory()->create(['label' => 'Warships NA']);
        $division = $this->createDivisionWithHandles([$na, $eu]);

        $this->assertSame(['Warships NA', 'Warships EU'], $division->handles->pluck('label')->all());
    }

    #[Test]
    public function handles_of_returns_only_the_members_division_handles_in_division_order()
    {
        $na       = Handle::factory()->create(['label' => 'Warships NA']);
        $eu       = Handle::factory()->create(['label' => 'Warships EU']);
        $other    = Handle::factory()->create();
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $member   = $this->createMember();

        $member->handles()->attach($eu->id, ['value' => 'eu_name', 'primary' => true]);
        $member->handles()->attach($other->id, ['value' => 'other', 'primary' => true]);
        $member->handles()->attach($na->id, ['value' => 'na_name', 'primary' => true]);

        $this->assertSame(['na_name', 'eu_name'], $division->handlesOf($member->fresh())->pluck('pivot.value')->all());
    }

    #[Test]
    public function handles_of_prefers_the_primary_value_when_a_member_has_several_of_one_type()
    {
        $handle   = Handle::factory()->create();
        $division = $this->createDivisionWithHandles([$handle]);
        $member   = $this->createMember();

        $member->handles()->attach($handle->id, ['value' => 'alt', 'primary' => false]);
        $member->handles()->attach($handle->id, ['value' => 'main', 'primary' => true]);

        $this->assertSame(['main'], $division->handlesOf($member->fresh())->pluck('pivot.value')->all());
    }

    #[Test]
    public function handle_summary_is_the_bare_value_for_a_single_handle_type()
    {
        $handle   = Handle::factory()->create();
        $division = $this->createDivisionWithHandles([$handle]);
        $member   = $this->createMember();
        $member->handles()->attach($handle->id, ['value' => 'solo', 'primary' => true]);

        $this->assertSame('solo', $division->handleSummaryFor($member->fresh()));
    }

    #[Test]
    public function handle_summary_labels_each_value_when_the_division_has_several_types()
    {
        $na       = Handle::factory()->create(['label' => 'NA']);
        $eu       = Handle::factory()->create(['label' => 'EU']);
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $member   = $this->createMember();
        $member->handles()->attach($na->id, ['value' => 'x', 'primary' => true]);
        $member->handles()->attach($eu->id, ['value' => 'y', 'primary' => true]);

        $this->assertSame('NA: x · EU: y', $division->handleSummaryFor($member->fresh()));
    }

    #[Test]
    public function handle_summary_is_null_when_the_member_has_none_of_the_divisions_handles()
    {
        $division = $this->createDivisionWithHandles([Handle::factory()->create()]);

        $this->assertNull($division->handleSummaryFor($this->createMember()));
    }

    #[Test]
    public function handle_field_uses_the_type_label_for_a_single_handle_type()
    {
        $handle   = Handle::factory()->create(['label' => 'Steam Profile']);
        $division = $this->createDivisionWithHandles([$handle]);
        $member   = $this->createMember();
        $member->handles()->attach($handle->id, ['value' => 'solo', 'primary' => true]);

        $this->assertSame(['name' => 'Steam Profile', 'value' => 'solo'], $division->handleFieldFor($member->fresh()));
    }

    #[Test]
    public function handle_field_lists_each_value_when_the_division_has_several_types()
    {
        $na       = Handle::factory()->create(['label' => 'NA']);
        $eu       = Handle::factory()->create(['label' => 'EU']);
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $member   = $this->createMember();
        $member->handles()->attach($na->id, ['value' => 'x', 'primary' => true]);
        $member->handles()->attach($eu->id, ['value' => 'y', 'primary' => true]);

        $this->assertSame(['name' => 'In-Game Handles', 'value' => "NA: x\nEU: y"], $division->handleFieldFor($member->fresh()));
    }

    #[Test]
    public function handle_field_falls_back_to_na_when_the_member_has_none()
    {
        $division = $this->createDivisionWithHandles([]);

        $this->assertSame(['name' => 'In-Game Handle', 'value' => 'N/A'], $division->handleFieldFor($this->createMember()));
    }
}
