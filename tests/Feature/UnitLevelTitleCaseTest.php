<?php

namespace Tests\Feature;

use App\Models\DivisionUnitLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;

class UnitLevelTitleCaseTest extends TestCase
{
    use CreatesDivisions;
    use RefreshDatabase;

    #[Test]
    public function level_names_are_stored_in_title_case(): void
    {
        $division = $this->createActiveDivision();

        $level = DivisionUnitLevel::create([
            'division_id'  => $division->id,
            'depth'        => 3,
            'label'        => 'fire team',
            'label_plural' => 'FIRE TEAMS',
            'leader_title' => 'fire team leader',
        ]);

        $this->assertSame('Fire Team', $level->label);
        $this->assertSame('Fire Teams', $level->label_plural);
        $this->assertSame('Fire Team Leader', $level->leader_title);
        $this->assertSame('Fire Team', DB::table('division_unit_levels')->where('id', $level->id)->value('label'));
    }

    #[Test]
    public function legacy_lowercase_names_print_in_title_case(): void
    {
        $division = $this->createActiveDivision();

        DB::table('division_unit_levels')->where('division_id', $division->id)->where('depth', 1)->update([
            'label'        => 'company',
            'label_plural' => 'companies',
            'leader_title' => 'company commander',
        ]);

        $division = $division->fresh();

        $this->assertSame('Company', $division->topLevelLabel());
        $this->assertSame('Companies', $division->topLevelPlural());
        $this->assertSame('Company Commander', $division->topLeaderTitle());
    }
}
