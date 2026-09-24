<?php

namespace Tests\Feature\Filament;

use App\Enums\DivisionMemberFieldType;
use App\Filament\Admin\Resources\DivisionMemberFieldResource\Pages\EditDivisionMemberField;
use App\Filament\Admin\Resources\DivisionMemberFieldResource\Pages\ListDivisionMemberFields;
use App\Models\DivisionMemberField;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionMemberFieldResourceTest extends TestCase
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
    public function admin_sees_member_fields_from_every_division(): void
    {
        $admin     = $this->createAdmin();
        $divisionA = $this->createActiveDivision();
        $divisionB = $this->createActiveDivision();
        $fieldA    = DivisionMemberField::create([
            'division_id' => $divisionA->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);
        $fieldB = DivisionMemberField::create([
            'division_id' => $divisionB->id,
            'key'         => 'zone',
            'label'       => 'Zone',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListDivisionMemberFields::class)
            ->assertCanSeeTableRecords([$fieldA, $fieldB]);
    }

    #[Test]
    public function admin_can_edit_a_field_without_changing_its_key_or_division(): void
    {
        $admin    = $this->createAdmin();
        $division = $this->createActiveDivision();
        $field    = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditDivisionMemberField::class, ['record' => $field->getRouteKey()])
            ->fillForm(['label' => 'Character Class'])
            ->call('save')
            ->assertHasNoFormErrors();

        $field->refresh();

        $this->assertSame('Character Class', $field->label);
        $this->assertSame('class', $field->key);
        $this->assertSame($division->id, $field->division_id);
    }

    #[Test]
    public function non_admin_cannot_access_the_admin_panel(): void
    {
        $member = $this->createMemberWithUser();

        $this->actingAs($member);

        $this->assertFalse($member->canAccessPanel(Filament::getPanel('admin')));
    }
}
