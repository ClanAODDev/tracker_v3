<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\RankActionResource\Pages\EditRankAction;
use App\Models\RankAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class RankActionReactionsVisibilityTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function reactions_are_hidden_from_division_leader_below_master_sergeant_for_sgt_plus_request(): void
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $leader->role = Role::SENIOR_LEADER;
        $leader->save();

        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->actingAs($leader);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->assertDontSee('Add reaction');
    }

    #[Test]
    public function reactions_are_visible_to_division_leader_at_master_sergeant_for_sgt_plus_request(): void
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::MASTER_SERGEANT,
        ]);
        $leader->role = Role::SENIOR_LEADER;
        $leader->save();

        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::SERGEANT,
        ]);

        $this->actingAs($leader);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->assertSee('Add reaction');
    }

    #[Test]
    public function reactions_remain_visible_to_division_leader_for_requests_below_sergeant(): void
    {
        $division = $this->createActiveDivision();
        $leader   = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::STAFF_SERGEANT,
        ]);
        $leader->role = Role::SENIOR_LEADER;
        $leader->save();

        $member = $this->createMember(['division_id' => $division->id]);

        $action = RankAction::factory()->create([
            'member_id' => $member->id,
            'rank'      => Rank::CORPORAL,
        ]);

        $this->actingAs($leader);

        Livewire::test(EditRankAction::class, ['record' => $action->getRouteKey()])
            ->assertSee('Add reaction');
    }
}
