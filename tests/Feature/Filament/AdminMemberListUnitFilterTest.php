<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\MemberResource\Pages\ListMembers;
use App\Models\Unit;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AdminMemberListUnitFilterTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function the_member_list_renders_when_units_have_no_name(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->createAdmin());

        $division = $this->createActiveDivision();
        $platoon  = Unit::factory()->create(['division_id' => $division->id, 'name' => null]);
        Unit::factory()->childOf($platoon)->create(['name' => '']);

        Livewire::test(ListMembers::class)
            ->assertSuccessful()
            ->assertSee('Untitled');
    }
}
