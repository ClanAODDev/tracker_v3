<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\LeaveResource\Pages\CreateLeave;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeaveResourceEndDateTest extends TestCase
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
    public function an_end_date_more_than_a_year_out_is_rejected(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->id,
                'reason'    => 'other',
                'end_date'  => now()->addYear()->addDay()->format('Y-m-d'),
                'note.body' => 'Test reason',
            ])
            ->call('create')
            ->assertHasFormErrors(['end_date']);

        $this->assertDatabaseMissing('leaves', ['member_id' => $member->id]);
    }

    #[Test]
    public function an_end_date_exactly_a_year_out_is_accepted(): void
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($officer);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->id,
                'reason'    => 'other',
                'end_date'  => now()->addYear()->format('Y-m-d'),
                'note.body' => 'Test reason',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leaves', ['member_id' => $member->id, 'reason' => 'other']);
    }
}
