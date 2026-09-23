<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionSettingsAccessTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function cpl_co_without_sr_ldr_role_can_access_division_settings(): void
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);

        $this->actingAs($co);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertSuccessful();
    }

    #[Test]
    public function sr_ldr_who_is_not_division_leader_cannot_access_division_settings(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMemberWithUser(
            ['division_id' => $division->id],
            ['role' => Role::SENIOR_LEADER],
        );

        $this->actingAs($member);

        $this->get(route('filament.mod.resources.divisions.edit', $division))
            ->assertForbidden();
    }
}
