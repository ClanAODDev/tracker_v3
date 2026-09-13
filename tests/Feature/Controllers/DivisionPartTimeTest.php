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
        $division = $this->createActiveDivision(['handle_id' => $handle->id]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();

        $this->actingAs($officer)
            ->post(route('addPartTimer', $division->slug), [
                'member_id'    => $member->clan_id,
                'handle_value' => '76561198000000000',
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
        $division = $this->createActiveDivision(['handle_id' => $handle->id]);
        $officer  = $this->createOfficer($division);
        $member   = $this->createMember();

        $response = $this->actingAs($officer)
            ->post(route('addPartTimer', $division->slug), [
                'member_id'    => $member->clan_id,
                'handle_value' => 'not-numeric',
            ]);

        $response->assertSessionHasErrors(['handle_value' => 'Steam ID must be numeric.']);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id]);
    }
}
