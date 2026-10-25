<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ModMemberLastTrainedAtTest extends TestCase
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
    public function changing_the_trainer_stamps_last_trained_at(): void
    {
        $division = $this->createActiveDivision();
        $this->actingAs($this->createSeniorLeader($division));

        $member  = $this->createMember(['division_id' => $division->id, 'last_trained_at' => null]);
        $trainer = $this->createMember(['division_id' => $division->id]);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['last_trained_by' => $trainer->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($member->fresh()->last_trained_at->isToday());
    }

    #[Test]
    public function saving_without_changing_the_trainer_leaves_last_trained_at_alone(): void
    {
        $division = $this->createActiveDivision();
        $this->actingAs($this->createSeniorLeader($division));

        $trainer = $this->createMember(['division_id' => $division->id]);
        $member  = $this->createMember([
            'division_id'     => $division->id,
            'last_trained_by' => $trainer->clan_id,
            'last_trained_at' => '2024-01-01',
        ]);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('2024-01-01', $member->fresh()->last_trained_at->toDateString());
    }

    #[Test]
    public function clearing_the_trainer_does_not_stamp_last_trained_at(): void
    {
        $division = $this->createActiveDivision();
        $this->actingAs($this->createSeniorLeader($division));

        $trainer = $this->createMember(['division_id' => $division->id]);
        $member  = $this->createMember([
            'division_id'     => $division->id,
            'last_trained_by' => $trainer->clan_id,
            'last_trained_at' => '2024-01-01',
        ]);

        $member->update(['last_trained_by' => null]);

        $this->assertSame('2024-01-01', $member->fresh()->last_trained_at->toDateString());
    }
}
