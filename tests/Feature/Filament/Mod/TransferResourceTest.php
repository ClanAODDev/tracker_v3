<?php

namespace Tests\Feature\Filament\Mod;

use App\Enums\Position;
use App\Filament\Mod\Resources\TransferResource;
use App\Filament\Mod\Resources\TransferResource\Pages\CreateTransfer;
use App\Filament\Mod\Resources\TransferResource\Pages\ListTransfers;
use App\Models\Division;
use App\Models\Member;
use App\Models\Transfer;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class TransferResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setModPanel();
    }

    public function test_admin_can_access_transfer_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(TransferResource::class);
    }

    public function test_officer_can_access_transfer_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCanAccessResource(TransferResource::class);
    }

    public function test_member_cannot_access_transfer_resource(): void
    {
        $this->actingAsMember();

        $this->assertCannotAccessResource(TransferResource::class);
    }

    public function test_can_list_transfers(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update(['division_id' => $toDivision->id]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->assertCanSeeTableRecords([$transfer]);
    }

    public function test_can_create_transfer(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update(['division_id' => $fromDivision->id]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);

        $this->actingAs($user);

        Livewire::test(CreateTransfer::class)
            ->fillForm([
                'member_id' => $member->id,
                'division_id' => $toDivision->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transfers', [
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
            'approved_at' => null,
        ]);
    }

    public function test_transfer_requires_member(): void
    {
        $toDivision = Division::factory()->create();
        $user = $this->createModUser();

        $this->actingAs($user);

        Livewire::test(CreateTransfer::class)
            ->fillForm([
                'member_id' => null,
                'division_id' => $toDivision->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['member_id' => 'required']);
    }

    public function test_transfer_requires_division(): void
    {
        $fromDivision = Division::factory()->create();
        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $user = $this->createModUser();

        $this->actingAs($user);

        Livewire::test(CreateTransfer::class)
            ->fillForm([
                'member_id' => $member->id,
                'division_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['division_id' => 'required']);
    }

    public function test_cannot_create_duplicate_pending_transfer(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update(['division_id' => $fromDivision->id]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);

        Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(CreateTransfer::class)
            ->fillForm([
                'member_id' => $member->id,
                'division_id' => $toDivision->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['member_id']);
    }

    public function test_division_leader_can_approve_transfer_to_their_division(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->callTableAction('Approve', $transfer);

        $this->assertDatabaseHas('transfers', [
            'id' => $transfer->id,
        ]);

        $transfer->refresh();
        $this->assertNotNull($transfer->approved_at);
    }

    public function test_can_place_hold_on_pending_transfer(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->callTableAction('Hold', $transfer);

        $this->assertDatabaseHas('transfers', [
            'id' => $transfer->id,
        ]);

        $transfer->refresh();
        $this->assertNotNull($transfer->hold_placed_at);
    }

    public function test_can_remove_hold_from_transfer(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->onHold()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->callTableAction('Remove Hold', $transfer);

        $this->assertDatabaseHas('transfers', [
            'id' => $transfer->id,
        ]);

        $transfer->refresh();
        $this->assertNull($transfer->hold_placed_at);
    }

    public function test_can_delete_pending_transfer(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update([
            'division_id' => $toDivision->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);
        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->callTableAction('delete', $transfer);

        $this->assertDatabaseMissing('transfers', [
            'id' => $transfer->id,
        ]);
    }

    public function test_approved_transfer_updates_member_division(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $member = Member::factory()->create([
            'division_id' => $fromDivision->id,
            'position' => Position::MEMBER,
        ]);

        $transfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $transfer->approve();

        $member->refresh();
        $this->assertEquals($toDivision->id, $member->division_id);
        $this->assertEquals(Position::MEMBER, $member->position);
    }

    public function test_incomplete_filter_shows_only_pending_transfers(): void
    {
        $fromDivision = Division::factory()->create();
        $toDivision = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update(['division_id' => $toDivision->id]);

        $member = Member::factory()->create(['division_id' => $fromDivision->id]);

        $pendingTransfer = Transfer::factory()->pending()->create([
            'member_id' => $member->id,
            'division_id' => $toDivision->id,
        ]);

        $approvedTransfer = Transfer::factory()->approved()->create([
            'member_id' => Member::factory()->create(['division_id' => $fromDivision->id])->id,
            'division_id' => $toDivision->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ListTransfers::class)
            ->assertCanSeeTableRecords([$pendingTransfer])
            ->assertCanNotSeeTableRecords([$approvedTransfer]);
    }
}
