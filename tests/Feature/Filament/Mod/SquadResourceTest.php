<?php

namespace Tests\Feature\Filament\Mod;

use App\Filament\Mod\Resources\SquadResource;
use App\Filament\Mod\Resources\SquadResource\Pages\EditSquad;
use App\Filament\Mod\Resources\SquadResource\Pages\ListSquads;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class SquadResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setModPanel();
    }

    public function test_admin_can_access_squad_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(SquadResource::class);
    }

    public function test_officer_can_access_squad_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCanAccessResource(SquadResource::class);
    }

    public function test_member_cannot_access_squad_resource(): void
    {
        $this->actingAsMember();

        $this->assertCannotAccessResource(SquadResource::class);
    }

    public function test_can_list_squads(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $squad = Squad::factory()->create(['platoon_id' => $platoon->id]);

        $this->actingAs($user);

        Livewire::test(ListSquads::class)
            ->assertCanSeeTableRecords([$squad]);
    }

    public function test_can_edit_squad(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $squad = Squad::factory()->create([
            'name' => 'Original Name',
            'platoon_id' => $platoon->id,
        ]);

        $this->actingAs($user);

        Livewire::test(EditSquad::class, ['record' => $squad->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('squads', [
            'id' => $squad->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_assign_leader_to_squad(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $squad = Squad::factory()->create(['platoon_id' => $platoon->id]);
        $leader = Member::factory()->create([
            'division_id' => $division->id,
            'platoon_id' => $platoon->id,
        ]);

        $this->actingAs($user);

        Livewire::test(EditSquad::class, ['record' => $squad->id])
            ->fillForm([
                'leader_id' => $leader->clan_id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('squads', [
            'id' => $squad->id,
            'leader_id' => $leader->clan_id,
        ]);
    }

    public function test_can_soft_delete_squad(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $squad = Squad::factory()->create(['platoon_id' => $platoon->id]);

        $this->actingAs($user);

        Livewire::test(EditSquad::class, ['record' => $squad->id])
            ->callAction('delete');

        $this->assertSoftDeleted('squads', [
            'id' => $squad->id,
        ]);
    }

    public function test_can_restore_soft_deleted_squad(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $squad = Squad::factory()->create(['platoon_id' => $platoon->id]);
        $squad->delete();

        $this->actingAs($user);

        Livewire::test(EditSquad::class, ['record' => $squad->id])
            ->callAction('restore');

        $this->assertDatabaseHas('squads', [
            'id' => $squad->id,
            'deleted_at' => null,
        ]);
    }
}
