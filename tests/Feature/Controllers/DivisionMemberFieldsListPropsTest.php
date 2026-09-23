<?php

namespace Tests\Feature\Controllers;

use App\Enums\DivisionMemberFieldType;
use App\Models\DivisionMemberField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionMemberFieldsListPropsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function member_field_definitions_and_values_appear_on_the_members_page(): void
    {
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $member   = $this->createMember(['division_id' => $division->id]);

        $field = DivisionMemberField::create([
            'division_id' => $division->id,
            'key'         => 'class',
            'label'       => 'Class',
            'type'        => DivisionMemberFieldType::SELECT,
            'options'     => [
                ['value' => 'Tank', 'color' => 'blue'],
                ['value' => 'Healer', 'color' => 'green'],
                ['value' => 'DPS', 'color' => 'red'],
            ],
            'filterable' => true,
        ]);
        $member->fieldValues()->create(['division_member_field_id' => $field->id, 'value' => 'Healer']);

        $this->actingAs($officer)
            ->get(route('division.members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('division/members')
                ->where('memberFields', fn ($fields) => collect($fields)->firstWhere('key', 'class')['colors'] === [
                    'Tank'   => 'blue',
                    'Healer' => 'green',
                    'DPS'    => 'red',
                ])
                ->where('members', fn ($members) => collect($members)
                    ->firstWhere('id', $member->clan_id)['customFields']['class'] === 'Healer'));
    }

    #[Test]
    public function fields_from_another_division_do_not_appear(): void
    {
        $officer       = $this->createOfficer();
        $division      = $officer->member->division;
        $otherDivision = $this->createActiveDivision();

        DivisionMemberField::create([
            'division_id' => $otherDivision->id,
            'key'         => 'realm',
            'label'       => 'Realm',
            'type'        => DivisionMemberFieldType::TEXT,
        ]);

        $this->actingAs($officer)
            ->get(route('division.members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('memberFields', fn ($fields) => collect($fields)->isEmpty()));
    }
}
