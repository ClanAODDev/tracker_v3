<?php

namespace Tests\Feature\Services\Units;

use App\Enums\ActivityType;
use App\Models\DivisionUnitLevel;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use App\Support\MemberRowSerializer;
use App\Support\UnitTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UnitAssignmentSingleLeadershipTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function naming_a_leader_releases_every_other_unit_they_led(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squadA   = $this->createSquad($platoon);
        $squadB   = $this->createSquad($platoon);
        $leader   = $this->createSquadLeader($squadA);

        app(UnitAssignment::class)->setLeader($squadB, $leader->clan_id);

        $this->assertNull($squadA->fresh()->leader_id);
        $this->assertSame($leader->clan_id, $squadB->fresh()->leader_id);
        $this->assertSame(1, Unit::where('leader_id', $leader->clan_id)->count());
    }

    #[Test]
    public function creating_a_unit_with_an_existing_leader_releases_their_old_unit(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $leader   = $this->createSquadLeader($squad);

        $new = app(UnitAssignment::class)->create($division, $platoon, ['name' => 'Fresh', 'leader_id' => $leader->clan_id]);

        $this->assertNull($squad->fresh()->leader_id);
        $this->assertSame($leader->clan_id, $new->fresh()->leader_id);
    }

    #[Test]
    public function assign_member_moves_the_member_and_records_the_activity(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember(['division_id' => $division->id]);
        $service  = app(UnitAssignment::class);
        $this->actingAs($this->createAdmin());

        $service->assignMember($member, $squad);
        $this->assertSame($squad->id, $member->fresh()->unit_id);
        $this->assertDatabaseHas('activities', ['name' => ActivityType::ASSIGNED_SQUAD->value, 'subject_id' => $member->id]);

        $service->assignMember($member, $platoon);
        $this->assertDatabaseHas('activities', ['name' => ActivityType::ASSIGNED_PLATOON->value, 'subject_id' => $member->id]);

        $service->assignMember($member, null);
        $this->assertNull($member->fresh()->unit_id);
        $this->assertDatabaseHas('activities', ['name' => ActivityType::UNASSIGNED->value, 'subject_id' => $member->id]);
    }

    #[Test]
    public function leader_abbreviations_follow_the_level_leader_titles(): void
    {
        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 1)->update(['leader_title' => 'Company Commander']);
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 2)->update(['leader_title' => 'Captain']);
        $division = $division->fresh();

        $platoon = $this->createPlatoon($division);
        $squad   = $this->createSquad($platoon);
        $pl      = $this->createPlatoonLeader($platoon)->fresh();
        $sl      = $this->createSquadLeader($squad)->fresh();

        $this->assertSame('CC', $pl->positionAbbreviation());
        $this->assertSame('CAP', $sl->positionAbbreviation());

        $this->actingAs($this->createAdmin());
        $rows = (new MemberRowSerializer($division))->collection(collect([$pl, $sl]));
        $this->assertSame(['CC', 'CAP'], array_column($rows, 'positionAbbr'));
    }

    #[Test]
    public function default_leader_abbreviations_are_unchanged(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);

        $this->assertSame('PL', $this->createPlatoonLeader($platoon)->fresh()->positionAbbreviation());
        $this->assertSame('SL', $this->createSquadLeader($squad)->fresh()->positionAbbreviation());
    }

    #[Test]
    public function the_unit_tree_nests_units_with_counts_and_leaders(): void
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $leader   = $this->createSquadLeader($squad);
        $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        $tree = UnitTree::for($division);

        $this->assertCount(1, $tree);
        $this->assertSame($platoon->id, $tree[0]['id']);
        $this->assertSame('Platoon', $tree[0]['levelLabel']);
        $this->assertSame($squad->id, $tree[0]['children'][0]['id']);
        $this->assertSame($leader->name, $tree[0]['children'][0]['leaderName']);
        $this->assertSame(2, $tree[0]['children'][0]['membersCount']);
        $this->assertSame(2, $tree[0]['membersCount']);
    }
}
