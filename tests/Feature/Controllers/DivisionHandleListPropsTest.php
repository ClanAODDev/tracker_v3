<?php

namespace Tests\Feature\Controllers;

use App\Models\Handle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionHandleListPropsTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function the_members_page_lists_every_division_handle_type_and_each_members_values(): void
    {
        $na       = Handle::factory()->create(['label' => 'Warships NA']);
        $eu       = Handle::factory()->create(['label' => 'Warships EU']);
        $other    = Handle::factory()->create();
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $division->handles()->sync([$na->id => ['sort_order' => 0], $eu->id => ['sort_order' => 1]]);

        $both   = $this->createMember(['division_id' => $division->id]);
        $euOnly = $this->createMember(['division_id' => $division->id]);
        $both->handles()->attach($na->id, ['value' => 'NaCaptain', 'primary' => true]);
        $both->handles()->attach($eu->id, ['value' => 'EuCaptain', 'primary' => true]);
        $euOnly->handles()->attach($eu->id, ['value' => 'EuOnly', 'primary' => true]);
        $euOnly->handles()->attach($other->id, ['value' => 'Hidden', 'primary' => true]);

        $this->actingAs($officer)
            ->get(route('division.members', $division->slug))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('division.handleTypes', fn ($types) => collect($types)->pluck('label')->all() === ['Warships NA', 'Warships EU'])
                ->where('members', function ($members) use ($both, $euOnly, $na, $eu) {
                    $rows = collect($members)->keyBy('id');

                    return $rows[$both->clan_id]['handles'][$na->id]['value'] === 'NaCaptain'
                        && $rows[$both->clan_id]['handles'][$eu->id]['value'] === 'EuCaptain'
                        && array_keys($rows[$euOnly->clan_id]['handles']) === [$eu->id];
                }));
    }

    #[Test]
    public function handles_that_fail_the_types_regex_are_flagged_in_member_rows(): void
    {
        $battlenet = Handle::factory()->create([
            'label'      => 'Battle.net',
            'regex'      => '/^\p{L}[\p{L}\p{N}]{2,11}#[0-9]{4,8}$/u',
            'regex_hint' => 'BattleTag must be in the form Name#1234.',
        ]);
        $officer  = $this->createOfficer();
        $division = $officer->member->division;
        $division->handles()->sync([$battlenet->id => ['sort_order' => 0]]);

        $valid   = $this->createMember(['division_id' => $division->id]);
        $invalid = $this->createMember(['division_id' => $division->id]);
        $valid->handles()->attach($battlenet->id, ['value' => 'Müller#12345', 'primary' => true]);
        $invalid->handles()->attach($battlenet->id, ['value' => 'NoDiscriminator', 'primary' => true]);

        $this->actingAs($officer)
            ->get(route('division.members', $division->slug))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('members', function ($members) use ($valid, $invalid, $battlenet) {
                    $rows = collect($members)->keyBy('id');

                    return $rows[$valid->clan_id]['handles'][$battlenet->id]['error'] === null
                        && $rows[$invalid->clan_id]['handles'][$battlenet->id]['error'] === 'BattleTag must be in the form Name#1234.';
                }));
    }
}
