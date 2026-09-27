<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Admin\Resources\DivisionResource;
use App\Filament\Admin\Resources\DivisionResource\Pages\CreateDivision;
use App\Filament\Admin\Resources\DivisionResource\Pages\EditDivision;
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
}
