<?php

namespace Tests\Feature\Controllers;

use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class BulkMoveDeepUnitsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function the_move_options_nest_every_unit_under_its_parent(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $company = $this->createPlatoon($division);
        $middle  = Unit::factory()->childOf($company)->create(['name' => 'Alpha']);
        $leaf    = Unit::factory()->childOf($middle)->create(['name' => 'One']);

        $this->actingAs($this->createSeniorLeader($division))
            ->getJson(route('bulk-transfer.platoons', $division->slug))
            ->assertOk()
            ->assertJsonPath('units.0.id', $company->id)
            ->assertJsonPath('units.0.children.0.id', $middle->id)
            ->assertJsonPath('units.0.children.0.children.0.id', $leaf->id)
            ->assertJsonPath('units.0.children.0.children.0.levelLabel', 'Team');
    }

    #[Test]
    public function members_can_be_moved_into_a_unit_at_any_depth(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::create(['division_id' => $division->id, 'depth' => 3, 'label' => 'Team', 'label_plural' => 'Teams', 'leader_title' => 'Team Leader']);
        $company = $this->createPlatoon($division);
        $middle  = Unit::factory()->childOf($company)->create();
        $leaf    = Unit::factory()->childOf($middle)->create();
        $member  = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createSeniorLeader($division))
            ->postJson(route('bulk-transfer.store', $division->slug), ['member_ids' => [$member->clan_id], 'unit_id' => $leaf->id])
            ->assertOk();

        $this->assertSame($leaf->id, $member->fresh()->unit_id);
    }

    #[Test]
    public function members_cannot_be_moved_into_another_divisions_unit(): void
    {
        $division = $this->createActiveDivision();
        $foreign  = $this->createPlatoon($this->createActiveDivision());
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createSeniorLeader($division))
            ->postJson(route('bulk-transfer.store', $division->slug), ['member_ids' => [$member->clan_id], 'unit_id' => $foreign->id])
            ->assertUnprocessable();

        $this->assertNull($member->fresh()->unit_id);
    }
}
