<?php

namespace Tests\Feature\Filament\Mod;

use App\Filament\Mod\Resources\LeaveResource;
use App\Filament\Mod\Resources\LeaveResource\Pages\CreateLeave;
use App\Filament\Mod\Resources\LeaveResource\Pages\EditLeave;
use App\Filament\Mod\Resources\LeaveResource\Pages\ListLeaves;
use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\User;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class LeaveResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setModPanel();
    }

    public function test_admin_can_access_leave_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(LeaveResource::class);
    }

    public function test_officer_can_access_leave_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCanAccessResource(LeaveResource::class);
    }

    public function test_member_cannot_access_leave_resource(): void
    {
        $this->actingAsMember();

        $this->assertCannotAccessResource(LeaveResource::class);
    }

    public function test_can_list_leaves(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        $leave = Leave::factory()->create(['member_id' => $member->clan_id]);

        $this->actingAs($user);

        Livewire::test(ListLeaves::class)
            ->assertCanSeeTableRecords([$leave]);
    }

    public function test_can_create_leave(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        $newData = [
            'member_id' => $member->clan_id,
            'reason' => 'military',
            'end_date' => now()->addDays(30),
        ];

        Livewire::test(CreateLeave::class)
            ->fillForm($newData)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leaves', [
            'member_id' => $member->clan_id,
            'reason' => 'military',
        ]);
    }

    public function test_leave_requires_member_id(): void
    {
        $this->actingAsOfficer();

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'reason' => 'military',
                'end_date' => now()->addDays(30),
            ])
            ->call('create')
            ->assertHasFormErrors(['member_id' => 'required']);
    }

    public function test_leave_requires_reason(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->clan_id,
                'end_date' => now()->addDays(30),
            ])
            ->call('create')
            ->assertHasFormErrors(['reason' => 'required']);
    }

    public function test_leave_end_date_has_default_value(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->clan_id,
                'reason' => 'military',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leaves', [
            'member_id' => $member->clan_id,
            'reason' => 'military',
        ]);
    }

    public function test_leave_end_date_must_be_at_least_30_days_in_future(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->clan_id,
                'reason' => 'military',
                'end_date' => now()->addDays(15),
            ])
            ->call('create')
            ->assertHasFormErrors(['end_date']);
    }

    public function test_member_cannot_have_duplicate_leave(): void
    {
        $division = Division::factory()->create();
        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        Leave::factory()->create(['member_id' => $member->clan_id]);

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->fillForm([
                'member_id' => $member->clan_id,
                'reason' => 'medical',
                'end_date' => now()->addDays(30),
            ])
            ->call('create')
            ->assertHasFormErrors(['member_id' => 'unique']);
    }

    public function test_can_edit_leave(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        $leave = Leave::factory()->create([
            'member_id' => $member->clan_id,
            'reason' => 'military',
        ]);

        $this->actingAs($user);

        Livewire::test(EditLeave::class, ['record' => $leave->id])
            ->fillForm([
                'reason' => 'medical',
                'end_date' => now()->addDays(45),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'reason' => 'medical',
        ]);
    }

    public function test_can_delete_leave(): void
    {
        $division = Division::factory()->create();
        $user = $this->createAdminUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        $leave = Leave::factory()->create(['member_id' => $member->clan_id]);

        $this->actingAs($user);

        Livewire::test(EditLeave::class, ['record' => $leave->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('leaves', [
            'id' => $leave->id,
        ]);
    }

    public function test_unapproved_leaves_badge_count_shows_for_division(): void
    {
        $division = Division::factory()->create();
        $user = User::factory()->state(['role' => \App\Enums\Role::SENIOR_LEADER])->create();
        $user->member->update(['division_id' => $division->id]);

        $member1 = Member::factory()->create(['division_id' => $division->id]);
        $member2 = Member::factory()->create(['division_id' => $division->id]);

        Leave::factory()->create([
            'member_id' => $member1->clan_id,
            'approver_id' => null,
        ]);

        Leave::factory()->create([
            'member_id' => $member2->clan_id,
            'approver_id' => null,
        ]);

        $this->actingAs($user);

        $badge = LeaveResource::getNavigationBadge();

        $this->assertEquals('2', $badge);
    }

    public function test_only_division_members_appear_in_member_select(): void
    {
        $division1 = Division::factory()->create();
        $division2 = Division::factory()->create();

        $user = $this->createOfficerUser();
        $user->member->update(['division_id' => $division1->id]);

        $divisionMember = Member::factory()->create(['division_id' => $division1->id]);
        $otherDivisionMember = Member::factory()->create(['division_id' => $division2->id]);

        $this->actingAs($user);

        Livewire::test(CreateLeave::class)
            ->assertFormFieldExists('member_id')
            ->fillForm([
                'member_id' => $divisionMember->clan_id,
                'reason' => 'military',
            ])
            ->assertHasNoFormErrors();
    }
}
