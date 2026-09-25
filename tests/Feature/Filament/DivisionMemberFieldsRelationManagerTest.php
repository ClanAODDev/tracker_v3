<?php

namespace Tests\Feature\Filament;

use App\Enums\DivisionMemberFieldType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Filament\Mod\Resources\DivisionResource\Pages\EditDivision;
use App\Filament\Mod\Resources\DivisionResource\RelationManagers\MemberFieldsRelationManager;
use App\Models\DivisionMemberField;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionMemberFieldsRelationManagerTest extends TestCase
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
    public function cpl_co_can_create_a_text_field(): void
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);

        $this->actingAs($co);

        Livewire::test(MemberFieldsRelationManager::class, [
            'ownerRecord' => $division,
            'pageClass'   => EditDivision::class,
        ])
            ->callTableAction('create', data: [
                'label'         => 'Class',
                'type'          => DivisionMemberFieldType::TEXT->value,
                'filterable'    => true,
                'self_editable' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('division_member_fields', [
            'division_id'   => $division->id,
            'key'           => 'class',
            'label'         => 'Class',
            'type'          => 'text',
            'self_editable' => true,
        ]);
    }

    #[Test]
    public function cpl_co_can_create_a_select_field_with_options(): void
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);

        $this->actingAs($co);

        Livewire::test(MemberFieldsRelationManager::class, [
            'ownerRecord' => $division,
            'pageClass'   => EditDivision::class,
        ])
            ->callTableAction('create', data: [
                'label'   => 'Role',
                'type'    => DivisionMemberFieldType::SELECT->value,
                'options' => [
                    ['value' => 'Tank', 'color' => 'blue'],
                    ['value' => 'Healer', 'color' => 'green'],
                    ['value' => 'DPS', 'color' => 'red'],
                ],
                'filterable' => true,
            ])
            ->assertHasNoTableActionErrors();

        $field = DivisionMemberField::where('division_id', $division->id)->where('key', 'role')->firstOrFail();

        $this->assertSame(DivisionMemberFieldType::SELECT, $field->type);
        $this->assertSame(['Tank', 'Healer', 'DPS'], $field->optionList());
        $this->assertSame(
            ['Tank' => 'blue', 'Healer' => 'green', 'DPS' => 'red'],
            $field->optionColors(),
        );
    }

    #[Test]
    public function creating_a_field_whose_key_already_exists_shows_a_validation_error(): void
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);
        DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'task_force',
            'label'       => 'Task Force',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($co);

        Livewire::test(MemberFieldsRelationManager::class, [
            'ownerRecord' => $division,
            'pageClass'   => EditDivision::class,
        ])
            ->callTableAction('create', data: [
                'label' => 'Task-Force',
                'type'  => DivisionMemberFieldType::TEXT->value,
            ])
            ->assertHasTableActionErrors(['label']);

        $this->assertSame(1, DivisionMemberField::where('division_id', $division->id)->count());
    }

    #[Test]
    public function the_same_field_key_can_exist_in_different_divisions(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $co            = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);
        DivisionMemberField::create([
            'division_id' => $otherDivision->id,
            'key'         => 'task_force',
            'label'       => 'Task Force',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($co);

        Livewire::test(MemberFieldsRelationManager::class, [
            'ownerRecord' => $division,
            'pageClass'   => EditDivision::class,
        ])
            ->callTableAction('create', data: [
                'label' => 'Task Force',
                'type'  => DivisionMemberFieldType::TEXT->value,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('division_member_fields', ['division_id' => $division->id, 'key' => 'task_force']);
    }

    #[Test]
    public function editing_a_field_cannot_change_its_key(): void
    {
        $division = $this->createActiveDivision();
        $co       = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);
        $field = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($co);

        Livewire::test(MemberFieldsRelationManager::class, [
            'ownerRecord' => $division,
            'pageClass'   => EditDivision::class,
        ])
            ->callTableAction('edit', $field, data: [
                'label'      => 'Class Renamed',
                'type'       => DivisionMemberFieldType::TEXT->value,
                'filterable' => true,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame('class', $field->fresh()->key);
    }

    #[Test]
    public function division_leader_cannot_create_fields_for_another_division(): void
    {
        $division      = $this->createActiveDivision();
        $otherDivision = $this->createActiveDivision();
        $co            = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'rank'        => Rank::CORPORAL,
        ], [
            'role' => Role::OFFICER,
        ]);

        $this->actingAs($co);

        $this->assertFalse($co->can('create', [DivisionMemberField::class, $otherDivision]));
    }

    #[Test]
    public function non_division_leader_cannot_create_fields(): void
    {
        $division = $this->createActiveDivision();
        $member   = $this->createMemberWithUser(
            ['division_id' => $division->id],
            ['role' => Role::SENIOR_LEADER],
        );

        $this->actingAs($member);

        $this->assertFalse($member->can('create', [DivisionMemberField::class, $division]));
    }
}
