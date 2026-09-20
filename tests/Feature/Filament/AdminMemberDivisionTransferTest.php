<?php

namespace Tests\Feature\Filament;

use App\Enums\Position;
use App\Filament\Admin\Resources\MemberResource\Pages\EditMember;
use App\Jobs\UpdateDivisionForMember;
use App\Models\Transfer;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AdminMemberDivisionTransferTest extends TestCase
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
    public function changing_division_on_the_admin_edit_form_creates_an_approved_transfer_and_syncs_the_forum(): void
    {
        Bus::fake();
        Notification::fake();

        $admin  = $this->createAdmin();
        $source = $this->createActiveDivision();
        $target = $this->createActiveDivision();
        $member = $this->createMember([
            'division_id' => $source->id,
            'platoon_id'  => 3,
            'squad_id'    => 7,
            'position'    => Position::MEMBER,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['division_id' => $target->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $member->refresh();
        $this->assertSame($target->id, $member->division_id);
        $this->assertSame(0, $member->platoon_id);
        $this->assertSame(0, $member->squad_id);

        $transfer = Transfer::where('member_id', $member->id)->first();
        $this->assertNotNull($transfer);
        $this->assertSame($target->id, $transfer->division_id);
        $this->assertNotNull($transfer->approved_at);

        Bus::assertDispatched(UpdateDivisionForMember::class, fn ($job) => $job->transfer->is($transfer));
    }

    #[Test]
    public function saving_the_admin_edit_form_without_changing_division_does_not_create_a_transfer(): void
    {
        Bus::fake();
        Notification::fake();

        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($admin);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['division_id' => $division->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('transfers', 0);
        Bus::assertNotDispatched(UpdateDivisionForMember::class);
    }
}
