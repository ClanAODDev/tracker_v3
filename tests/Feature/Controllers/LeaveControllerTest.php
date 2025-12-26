<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeaveControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_index_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('leave.index', $division->slug));

        $response->assertRedirect('/login');
    }

    public function test_store_validates_member_belongs_to_division()
    {
        $srLdr = $this->createSeniorLeader();
        $division = $srLdr->member->division;
        $otherDivision = $this->createActiveDivision();
        $member = $this->createMember(['division_id' => $otherDivision->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('leave.store', $division->slug), [
                'member_id' => $member->clan_id,
                'end_date' => now()->addMonth()->format('Y-m-d'),
                'reason' => 'Test reason',
            ]);

        $response->assertSessionHasErrors('member_id');
    }
}
