<?php

namespace Tests\Unit\Support;

use App\Models\Unit;
use App\Support\MemberRowSerializer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberRowSerializerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function units_list_the_trail_from_the_top_level_down(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $platoon  = $this->createPlatoon($division, ['name' => 'Alpha']);
        $squad    = Unit::factory()->childOf($platoon)->create(['name' => '']);
        $member   = $this->createMember(['division_id' => $division->id, 'unit_id' => $squad->id]);

        $this->actingAs($officer);
        $row = (new MemberRowSerializer($division))->collection(collect([$member]))[0];

        $this->assertSame([[$platoon->id, 1, 'Alpha'], [$squad->id, 2, 'Untitled']], array_map(
            fn (array $unit) => [$unit['id'], $unit['depth'], $unit['label']],
            $row['units'],
        ));
    }

    #[Test]
    public function a_part_timer_has_no_units_from_their_primary_division(): void
    {
        $officer   = $this->createOfficer();
        $division  = $officer->member->division;
        $primary   = $this->createActiveDivision();
        $platoon   = $this->createPlatoon($primary);
        $parttimer = $this->createMember(['division_id' => $primary->id, 'unit_id' => $platoon->id]);

        $this->actingAs($officer);

        $this->assertSame([], (new MemberRowSerializer($division))->collection(collect([$parttimer]))[0]['units']);
    }
}
