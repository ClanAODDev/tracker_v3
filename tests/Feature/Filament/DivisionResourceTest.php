<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Admin\Resources\DivisionResource;
use App\Filament\Admin\Resources\DivisionResource\Pages\CreateDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\EditDivision;
use App\Models\Handle;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionResourceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('admin');
    }

    #[Test]
    public function plain_admin_cannot_bulk_delete_divisions()
    {
        $admin = $this->createAdmin(userAttributes: ['developer' => false]);

        $this->actingAs($admin);

        $this->assertFalse(DivisionResource::canDeleteAny());
    }

    #[Test]
    public function developer_can_bulk_delete_divisions()
    {
        $developer = $this->createAdmin();

        $this->actingAs($developer);

        $this->assertTrue(DivisionResource::canDeleteAny());
    }

    #[Test]
    public function cannot_create_a_division_with_the_name_of_an_existing_active_division()
    {
        $admin = $this->createAdmin();
        $this->createActiveDivision(['name' => 'Battlefield']);

        $this->actingAs($admin);

        Livewire::test(CreateDivision::class)
            ->fillForm(['name' => 'Battlefield'])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    #[Test]
    public function can_reuse_the_name_of_a_soft_deleted_division()
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision(['name' => 'Battlefield']);
        $division->delete();

        $this->actingAs($admin);

        Livewire::test(CreateDivision::class)
            ->fillForm(['name' => 'Battlefield'])
            ->call('create')
            ->assertHasNoFormErrors(['name']);
    }

    #[Test]
    public function a_platoon_leader_cannot_be_made_an_executive_officer()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $leader   = $this->createPlatoonLeader($platoon);

        $this->actingAs($this->createAdmin());

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->fillForm(['executive_officers' => [['xo' => $leader->id]]])
            ->call('save')
            ->assertHasFormErrors(['executive_officers.0.xo']);

        $this->assertEquals(Position::PLATOON_LEADER, $leader->fresh()->position);
        $this->assertEquals($leader->clan_id, $platoon->fresh()->leader_id);
    }

    #[Test]
    public function a_squad_leader_cannot_be_made_commanding_officer()
    {
        $division = $this->createActiveDivision();
        $squad    = $this->createSquad($this->createPlatoon($division));
        $leader   = $this->createSquadLeader($squad);

        $this->actingAs($this->createAdmin());

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->fillForm(['new_co' => $leader->id])
            ->call('save')
            ->assertHasFormErrors(['new_co']);

        $this->assertEquals(Position::SQUAD_LEADER, $leader->fresh()->position);
    }

    #[Test]
    public function new_co_cannot_also_be_listed_as_an_executive_officer()
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($this->createAdmin());

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->fillForm([
                'new_co'             => $member->id,
                'executive_officers' => [['xo' => $member->id]],
            ])
            ->call('save')
            ->assertHasFormErrors(['new_co']);

        $this->assertEquals(Position::MEMBER, $member->fresh()->position);
    }

    #[Test]
    public function an_xo_removed_from_the_xo_list_can_be_selected_as_co()
    {
        $division = $this->createActiveDivision();
        $xo       = $this->createExecutiveOfficer($division);

        $this->actingAs($this->createAdmin());

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->fillForm([
                'new_co'             => $xo->id,
                'executive_officers' => [],
            ])
            ->call('save')
            ->assertHasNoFormErrors(['new_co']);
    }

    #[Test]
    public function new_xo_has_stale_platoon_and_squad_leadership_cleared()
    {
        $division = $this->createActiveDivision();
        $platoon  = $this->createPlatoon($division);
        $squad    = $this->createSquad($platoon);
        $member   = $this->createMember(['division_id' => $division->id]);

        $page = Livewire::actingAs($this->createAdmin())
            ->test(EditDivision::class, ['record' => $division->getRouteKey()]);

        $platoon->update(['leader_id' => $member->clan_id]);
        $squad->update(['leader_id' => $member->clan_id]);

        (new \ReflectionMethod(EditDivision::class, 'handleXOs'))
            ->invoke($page->instance(), $division->id, ['executive_officers' => [['xo' => $member->id]]]);

        $this->assertEquals(Position::EXECUTIVE_OFFICER, $member->fresh()->position);
        $this->assertNull($platoon->fresh()->leader_id);
        $this->assertNull($squad->fresh()->leader_id);
    }

    #[Test]
    public function warns_when_an_active_division_uses_a_disabled_handle_type()
    {
        $this->actingAs($this->createAdmin());
        $disabled = Handle::factory()->create(['label' => 'Warships EU', 'enabled' => false]);
        $division = $this->createDivisionWithHandles([$disabled]);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertSee('This handle type is disabled');
    }

    #[Test]
    public function does_not_warn_about_disabled_handle_types_on_an_inactive_division()
    {
        $this->actingAs($this->createAdmin());
        $disabled = Handle::factory()->create(['enabled' => false]);
        $division = $this->createDivisionWithHandles([$disabled], ['active' => false]);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertDontSee('This handle type is disabled');
    }

    #[Test]
    public function does_not_warn_about_enabled_handle_types()
    {
        $this->actingAs($this->createAdmin());
        $division = $this->createDivisionWithHandles([Handle::factory()->create(['enabled' => true])]);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertDontSee('This handle type is disabled');
    }

    #[Test]
    public function the_warning_offers_to_enable_the_handle_type()
    {
        $this->actingAs($this->createAdmin());
        $disabled = Handle::factory()->create(['enabled' => false]);
        $division = $this->createDivisionWithHandles([$disabled]);

        $page    = Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()]);
        $itemKey = array_key_first($page->get('data.handleAssignments'));

        $page->callAction(TestAction::make('enableHandleType')->schemaComponent("handleAssignments.{$itemKey}.handle_id"));

        $this->assertTrue($disabled->fresh()->enabled);
    }
}
