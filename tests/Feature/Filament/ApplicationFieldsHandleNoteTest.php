<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Models\Division;
use App\Models\Handle;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesMembers;

class ApplicationFieldsHandleNoteTest extends TestCase
{
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function the_application_fields_section_says_which_handles_are_asked_automatically(): void
    {
        $division = Division::factory()
            ->withHandles(
                Handle::factory()->create(['label' => 'Warships NA']),
                Handle::factory()->create(['label' => 'Warships EU']),
            )
            ->create(['active' => true]);

        $co = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], ['role' => Role::OFFICER]);

        $this->actingAs($co);

        Livewire::test(EditDivision::class, ['record' => $division->getRouteKey()])
            ->assertSee('Applicants are automatically asked for which of Warships NA or Warships EU they play on');
    }
}
