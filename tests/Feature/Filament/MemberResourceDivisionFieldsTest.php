<?php

namespace Tests\Feature\Filament;

use App\Enums\DivisionMemberFieldType;
use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Models\DivisionMemberField;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceDivisionFieldsTest extends TestCase
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
    public function sr_ldr_can_save_a_text_field_value(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['custom_fields' => ['class' => 'Warlock']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Warlock',
        ]);
    }

    #[Test]
    public function sr_ldr_can_save_a_select_field_value(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'role',
            'label'       => 'Role',
            'type'        => DivisionMemberFieldType::SELECT,
            'options'     => [
                ['value' => 'Tank', 'color' => 'blue'],
                ['value' => 'Healer', 'color' => 'green'],
                ['value' => 'DPS', 'color' => 'red'],
            ],
        ]);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['custom_fields' => ['role' => 'Healer']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
            'value'                    => 'Healer',
        ]);
    }

    #[Test]
    public function existing_field_values_are_loaded_onto_the_form(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $field->id, 'value' => 'Rogue']);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->assertFormSet(['custom_fields' => ['class' => 'Rogue']]);
    }

    #[Test]
    public function clearing_a_field_value_removes_the_row(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $field->id, 'value' => 'Rogue']);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['custom_fields' => ['class' => '']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('member_field_values', [
            'member_id'                => $member->id,
            'division_member_field_id' => $field->id,
        ]);
    }

    #[Test]
    public function fields_from_a_different_division_are_not_shown_or_saved(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $srLdr         = $this->createSeniorLeader($division);
        $member        = $this->createMember(['division_id' => $division->id]);
        DivisionMemberField::create([
            'division_id' => $otherDivision->id,
            'key'         => 'realm',
            'label'       => 'Realm',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['custom_fields' => ['realm' => 'Should not persist']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('member_field_values', [
            'member_id' => $member->id,
        ]);
    }
}
