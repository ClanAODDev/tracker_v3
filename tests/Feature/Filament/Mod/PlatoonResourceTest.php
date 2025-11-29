<?php

namespace Tests\Feature\Filament\Mod;

use App\Filament\Mod\Resources\PlatoonResource;
use App\Filament\Mod\Resources\PlatoonResource\Pages\EditPlatoon;
use App\Filament\Mod\Resources\PlatoonResource\Pages\ListPlatoons;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class PlatoonResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setModPanel();
    }

    public function test_admin_can_access_platoon_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(PlatoonResource::class);
    }

    public function test_officer_can_access_platoon_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCanAccessResource(PlatoonResource::class);
    }

    public function test_member_cannot_access_platoon_resource(): void
    {
        $this->actingAsMember();

        $this->assertCannotAccessResource(PlatoonResource::class);
    }

    public function test_can_list_platoons(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(ListPlatoons::class)
            ->assertCanSeeTableRecords([$platoon]);
    }

    public function test_can_edit_platoon(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create([
            'name' => 'Original Name',
            'division_id' => $division->id,
        ]);

        $this->actingAs($user);

        Livewire::test(EditPlatoon::class, ['record' => $platoon->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('platoons', [
            'id' => $platoon->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_assign_leader_to_platoon(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $leader = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditPlatoon::class, ['record' => $platoon->id])
            ->fillForm([
                'leader_id' => $leader->clan_id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('platoons', [
            'id' => $platoon->id,
            'leader_id' => $leader->clan_id,
        ]);
    }

    public function test_can_soft_delete_platoon(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditPlatoon::class, ['record' => $platoon->id])
            ->callAction('delete');

        $this->assertSoftDeleted('platoons', [
            'id' => $platoon->id,
        ]);
    }

    public function test_can_restore_soft_deleted_platoon(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $platoon->delete();

        $this->actingAs($user);

        Livewire::test(EditPlatoon::class, ['record' => $platoon->id])
            ->callAction('restore');

        $this->assertDatabaseHas('platoons', [
            'id' => $platoon->id,
            'deleted_at' => null,
        ]);
    }

    public function test_leadership_section_visible_on_edit(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditPlatoon::class, ['record' => $platoon->id])
            ->assertFormFieldExists('leader_id');
    }
}
