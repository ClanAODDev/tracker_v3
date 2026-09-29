<?php

namespace Tests\Feature\Controllers;

use App\Models\Handle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionPartTimeTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function a_part_timer_can_be_added_with_a_handle_value_matching_the_divisions_format()
    {
        $handle   = Handle::create(['label' => 'Steam', 'regex' => '/^[0-9]+$/']);
        $division = $this->createDivisionWithHandles([$handle]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();

        $this->actingAs($officer)
            ->post(route('addPartTimer', $division->slug), [
                'member_id' => $member->clan_id,
                'handles'   => [$handle->id => '76561198000000000'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => '76561198000000000',
        ]);
    }

    #[Test]
    public function adding_a_part_timer_rejects_a_handle_value_that_fails_the_divisions_format()
    {
        $handle = Handle::create([
            'label'      => 'Steam',
            'regex'      => '/^[0-9]+$/',
            'regex_hint' => 'Steam ID must be numeric.',
        ]);
        $division = $this->createDivisionWithHandles([$handle]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();

        $response = $this->actingAs($officer)
            ->post(route('addPartTimer', $division->slug), [
                'member_id' => $member->clan_id,
                'handles'   => [$handle->id => 'not-numeric'],
            ]);

        $response->assertSessionHasErrors(["handles.{$handle->id}" => 'Steam ID must be numeric.']);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id]);
    }

    #[Test]
    public function a_part_timer_can_be_added_with_one_of_several_division_handles()
    {
        $na       = Handle::create(['label' => 'Warships NA']);
        $eu       = Handle::create(['label' => 'Warships EU']);
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();

        $this->actingAs($officer)
            ->post(route('addPartTimer', $division->slug), [
                'member_id' => $member->clan_id,
                'handles'   => [$na->id => '', $eu->id => 'EuCaptain'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handle_member', ['member_id' => $member->id, 'handle_id' => $eu->id, 'value' => 'EuCaptain']);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id, 'handle_id' => $na->id]);
    }

    #[Test]
    public function the_part_timer_page_lists_each_division_handle_a_member_has()
    {
        $na       = Handle::create(['label' => 'Warships NA']);
        $eu       = Handle::create(['label' => 'Warships EU']);
        $division = $this->createDivisionWithHandles([$na, $eu]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();
        $division->partTimeMembers()->attach($member->id);
        $member->handles()->attach($na->id, ['value' => 'NaCaptain', 'primary' => true]);
        $member->handles()->attach($eu->id, ['value' => 'EuCaptain', 'primary' => true]);

        $this->actingAs($officer)
            ->get(route('partTimers', $division->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('division.handleTypes.0.label', 'Warships NA')
                ->where('division.handleTypes.1.label', 'Warships EU')
                ->where('members.0.handles.0.value', 'NaCaptain')
                ->where('members.0.handles.1.value', 'EuCaptain'));
    }
}
