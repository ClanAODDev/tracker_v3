<?php

namespace Tests\Feature\Filament\Mod;

use App\Enums\Position;
use App\Filament\Mod\Resources\MemberResource;
use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Filament\Mod\Resources\MemberResource\Pages\ListMembers;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use Livewire\Livewire;
use Tests\FilamentTestCase;

final class MemberResourceTest extends FilamentTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setModPanel();
    }

    public function test_admin_can_access_member_resource(): void
    {
        $this->actingAsAdmin();

        $this->assertCanAccessResource(MemberResource::class);
    }

    public function test_officer_can_access_member_resource(): void
    {
        $this->actingAsOfficer();

        $this->assertCanAccessResource(MemberResource::class);
    }

    public function test_member_cannot_access_member_resource(): void
    {
        $this->actingAsMember();

        $this->assertCannotAccessResource(MemberResource::class);
    }

    public function test_cannot_create_member_directly(): void
    {
        $this->assertFalse(MemberResource::canCreate());
    }

    public function test_can_list_members(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(ListMembers::class)
            ->assertCanSeeTableRecords([$member, $user->member]);
    }

    public function test_can_edit_member_recruiter(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        $recruiter = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->fillForm([
                'recruiter_id' => $recruiter->clan_id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('members', [
            'clan_id' => $member->clan_id,
            'recruiter_id' => $recruiter->clan_id,
        ]);
    }

    public function test_can_update_member_trainer(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);
        $trainer = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->fillForm([
                'last_trained_by' => $trainer->clan_id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('members', [
            'clan_id' => $member->clan_id,
            'last_trained_by' => $trainer->clan_id,
        ]);
    }

    public function test_position_field_is_disabled(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create([
            'division_id' => $division->id,
            'position' => Position::MEMBER,
        ]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->assertFormFieldIsDisabled('position');
    }

    public function test_can_assign_member_to_platoon(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->fillForm([
                'platoon_id' => $platoon->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('members', [
            'clan_id' => $member->clan_id,
            'platoon_id' => $platoon->id,
        ]);
    }

    public function test_can_assign_member_to_squad(): void
    {
        $division = Division::factory()->create();
        $platoon = Platoon::factory()->create(['division_id' => $division->id]);
        $squad = Squad::factory()->create(['platoon_id' => $platoon->id]);

        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create([
            'division_id' => $division->id,
            'platoon_id' => $platoon->id,
        ]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->fillForm([
                'squad_id' => $squad->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('members', [
            'clan_id' => $member->clan_id,
            'squad_id' => $squad->id,
        ]);
    }

    public function test_communication_fields_are_disabled(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->assertFormFieldIsDisabled('ts_unique_id')
            ->assertFormFieldIsDisabled('discord')
            ->assertFormFieldIsDisabled('discord_id');
    }

    public function test_activity_fields_are_readonly(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->assertFormFieldExists('last_voice_activity')
            ->assertFormFieldExists('last_activity');
    }

    public function test_date_fields_are_disabled(): void
    {
        $division = Division::factory()->create();
        $user = $this->createModUser();
        $user->member->update(['division_id' => $division->id]);

        $member = Member::factory()->create(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(EditMember::class, ['record' => $member->clan_id])
            ->assertFormFieldIsDisabled('join_date')
            ->assertFormFieldIsDisabled('last_promoted_at')
            ->assertFormFieldIsDisabled('last_trained_at');
    }

    public function test_can_filter_members_by_division(): void
    {
        $division1 = Division::factory()->create();
        $division2 = Division::factory()->create();

        $user = $this->createModUser();
        $user->member->update(['division_id' => $division1->id]);

        $member1 = Member::factory()->create(['division_id' => $division1->id]);
        $member2 = Member::factory()->create(['division_id' => $division2->id]);

        $this->actingAs($user);

        Livewire::test(ListMembers::class)
            ->filterTable('division', $division1->id)
            ->assertCanSeeTableRecords([$member1, $user->member])
            ->assertCanNotSeeTableRecords([$member2]);
    }
}
