<?php

namespace Tests\Feature\Controllers;

use App\Enums\DivisionMemberFieldType;
use App\Models\DivisionMemberField;
use App\Models\Handle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberUpdateDetailsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function squad_leader_can_update_a_squad_members_handle_and_field(): void
    {
        $squad    = $this->createSquad();
        $division = $squad->platoon->division;
        $leader   = $this->createSquadLeader($squad);
        $user     = User::factory()->create(['member_id' => $leader->id, 'name' => $leader->name]);
        $member   = $this->createMember(['division_id' => $division->id, 'squad_id' => $squad->id]);
        $handle   = Handle::factory()->create(['enabled' => true, 'regex' => null]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $member->clan_id), [
                'handles' => [
                    ['handle_id' => $handle->id, 'value' => 'SomeGamerTag', 'primary' => true],
                ],
                'fields' => ['class' => 'Mage'],
            ])
            ->assertOk();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => 'SomeGamerTag',
        ]);
        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Mage',
        ]);
    }

    #[Test]
    public function member_can_always_update_their_own_handles(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $handle   = Handle::factory()->create(['enabled' => true, 'regex' => null]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $user->member->clan_id), [
                'handles' => [
                    ['handle_id' => $handle->id, 'value' => 'MyOwnTag', 'primary' => true],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $user->member->id,
            'handle_id' => $handle->id,
            'value'     => 'MyOwnTag',
        ]);
    }

    #[Test]
    public function member_cannot_update_their_own_field_unless_that_field_is_self_editable(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id'   => $division->id,
            'key'           => 'class',
            'label'         => 'Class',
            'type'          => DivisionMemberFieldType::TEXT,
            'self_editable' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $user->member->clan_id), [
                'fields' => ['class' => 'Mage'],
            ])
            ->assertForbidden();

        $field->update(['self_editable' => true]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $user->member->clan_id), [
                'fields' => ['class' => 'Mage'],
            ])
            ->assertOk();

        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $user->member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Mage',
        ]);
    }

    #[Test]
    public function member_cannot_touch_a_non_self_editable_field_even_alongside_one_they_can(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $zone     = DivisionMemberField::create([
            'division_id'   => $division->id,
            'key'           => 'zone',
            'label'         => 'Zone',
            'type'          => DivisionMemberFieldType::TEXT,
            'self_editable' => true,
        ]);
        $rating = DivisionMemberField::create([
            'division_id'   => $division->id,
            'key'           => 'rating',
            'label'         => 'Rating',
            'type'          => DivisionMemberFieldType::TEXT,
            'self_editable' => false,
        ]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $user->member->clan_id), [
                'fields' => ['zone' => 'US', 'rating' => 'A'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.rating']);

        $this->assertDatabaseMissing('member_field_values', ['division_member_field_id' => $zone->id]);
        $this->assertDatabaseMissing('member_field_values', ['division_member_field_id' => $rating->id]);
    }

    #[Test]
    public function unrelated_member_cannot_update_another_members_details(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($user)
            ->postJson(route('member.update-details', $member->clan_id), [
                'fields' => ['class' => 'Mage'],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function saving_one_field_does_not_clear_a_sibling_field(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $role     = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'role',
            'label'       => 'Role',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $zone = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'zone',
            'label'       => 'Zone',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $role->id, 'value' => 'Tank']);
        $member->fieldValues()->create(['division_member_field_id' => $zone->id, 'value' => 'US']);

        $this->actingAs($officer)
            ->postJson(route('member.update-details', $member->clan_id), [
                'fields' => ['zone' => 'EU'],
            ])
            ->assertOk();

        $this->assertDatabaseHas('member_field_values', [
            'division_member_field_id' => $role->id,
            'value'                    => 'Tank',
        ]);
        $this->assertDatabaseHas('member_field_values', [
            'division_member_field_id' => $zone->id,
            'value'                    => 'EU',
        ]);
    }

    #[Test]
    public function saving_a_field_does_not_clear_the_members_handles(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $handle   = Handle::factory()->create(['enabled' => true, 'regex' => null]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $member->handles()->attach($handle->id, ['value' => 'ExistingTag']);

        $this->actingAs($officer)
            ->postJson(route('member.update-details', $member->clan_id), [
                'fields' => ['class' => 'Mage'],
            ])
            ->assertOk();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => 'ExistingTag',
        ]);
        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Mage',
        ]);
    }

    #[Test]
    public function saving_handles_does_not_clear_the_members_fields(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $handle   = Handle::factory()->create(['enabled' => true, 'regex' => null]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $field->id, 'value' => 'Mage']);

        $this->actingAs($officer)
            ->postJson(route('member.update-details', $member->clan_id), [
                'handles' => [
                    ['handle_id' => $handle->id, 'value' => 'NewTag', 'primary' => true],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => 'NewTag',
        ]);
        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Mage',
        ]);
    }

    #[Test]
    public function a_select_field_rejects_a_value_outside_its_defined_options(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'role',
            'label'       => 'Role',
            'type'        => DivisionMemberFieldType::SELECT,
            'options'     => [['value' => 'Tank', 'color' => 'blue']],
        ]);

        $this->actingAs($officer)
            ->postJson(route('member.update-details', $member->clan_id), [
                'fields' => ['role' => 'NotARealOption'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.role']);
    }

    #[Test]
    public function a_handle_value_failing_its_format_is_rejected(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);
        $handle   = Handle::factory()->create([
            'enabled'    => true,
            'regex'      => '/^\d+$/',
            'regex_hint' => 'Must be numeric',
        ]);

        $this->actingAs($officer)
            ->postJson(route('member.update-details', $member->clan_id), [
                'handles' => [
                    ['handle_id' => $handle->id, 'value' => 'not-numeric', 'primary' => true],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['handles.0.value']);

        $this->assertDatabaseMissing('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
        ]);
    }
}
