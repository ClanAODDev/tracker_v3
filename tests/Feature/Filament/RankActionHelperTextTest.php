<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\RankActionResource\Pages\CreateRankAction;
use App\Models\DivisionUnitLevel;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionHelperTextTest extends TestCase
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
    public function the_helper_text_uses_the_divisions_level_label_and_configured_rank_ceiling(): void
    {
        config(['aod.rank.max_squad_leader' => Rank::LANCE_CORPORAL]);

        $division = $this->createActiveDivision();
        DivisionUnitLevel::where('division_id', $division->id)->where('depth', 2)->update(['label' => 'Team']);

        $squad  = $this->createSquad($this->createPlatoon($division));
        $leader = $this->createMemberWithUser([
            'division_id' => $division->id,
            'unit_id'     => $squad->id,
            'position'    => Position::SQUAD_LEADER,
            'rank'        => Rank::SERGEANT,
        ], ['role' => Role::OFFICER]);
        $this->makeLeader($leader, $squad);

        $this->actingAs($leader);

        Livewire::test(CreateRankAction::class)
            ->assertSee('Only team members up to Tr can be selected.');
    }
}
