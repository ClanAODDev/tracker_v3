<?php

namespace Tests\Feature\Migrations;

use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ConvertForumIdMemberReferencesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function forum_id_references_become_member_ids()
    {
        $recruiter = $this->createMember(['clan_id' => 21824]);
        $trainer   = $this->createMember(['clan_id' => 3550]);
        $recruit   = $this->createMember();
        $unit      = Unit::factory()->create(['division_id' => $this->createActiveDivision()->id]);

        DB::table('members')->where('id', $recruit->id)->update(['recruiter_id' => 21824, 'last_trained_by' => 3550]);
        DB::table('units')->where('id', $unit->id)->update(['leader_id' => 21824]);

        $this->runConversion();

        $this->assertDatabaseHas('members', ['id' => $recruit->id, 'recruiter_member_id' => $recruiter->id, 'trainer_member_id' => $trainer->id]);
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'leader_member_id' => $recruiter->id]);
    }

    #[Test]
    public function a_forum_id_that_equals_another_members_internal_id_maps_by_forum_id()
    {
        $recruiter = $this->createMember(['clan_id' => 900002]);
        $decoy     = $this->createMember(['clan_id' => 900003]);
        $recruit   = $this->createMember();

        DB::table('members')->where('id', $recruit->id)->update(['recruiter_id' => 900002]);
        DB::table('members')->where('id', $decoy->id)->update(['id' => 900002]);

        $this->runConversion();

        $this->assertSame($recruiter->id, DB::table('members')->where('id', $recruit->id)->value('recruiter_member_id'));
    }

    #[Test]
    public function unmatched_and_empty_forum_ids_are_left_null()
    {
        $recruit = $this->createMember();
        $other   = $this->createMember();

        DB::table('members')->where('id', $recruit->id)->update(['recruiter_id' => 777777, 'last_trained_by' => 0]);
        DB::table('members')->where('id', $other->id)->update(['recruiter_id' => null]);

        $this->runConversion();

        $this->assertDatabaseHas('members', ['id' => $recruit->id, 'recruiter_member_id' => null, 'trainer_member_id' => null]);
        $this->assertDatabaseHas('members', ['id' => $other->id, 'recruiter_member_id' => null]);
    }

    #[Test]
    public function the_conversion_never_overwrites_an_existing_member_reference()
    {
        $this->createMember(['clan_id' => 21824]);
        $current = $this->createMember();
        $recruit = $this->createMember();

        DB::table('members')->where('id', $recruit->id)->update(['recruiter_id' => 21824, 'recruiter_member_id' => $current->id]);

        $this->runConversion();

        $this->assertSame($current->id, DB::table('members')->where('id', $recruit->id)->value('recruiter_member_id'));
    }

    #[Test]
    public function running_the_conversion_twice_gives_the_same_result()
    {
        $recruiter = $this->createMember(['clan_id' => 21824]);
        $recruit   = $this->createMember();

        DB::table('members')->where('id', $recruit->id)->update(['recruiter_id' => 21824]);

        $this->runConversion();
        $this->runConversion();

        $this->assertSame($recruiter->id, DB::table('members')->where('id', $recruit->id)->value('recruiter_member_id'));
    }

    private function runConversion(): void
    {
        (require database_path('migrations/2026_11_16_000002_convert_forum_id_member_references.php'))->convert();
    }
}
