<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LeaveControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function index_requires_authentication()
    {
        $division = $this->createActiveDivision();

        $response = $this->get(route('leave.index', $division->slug));

        $response->assertRedirect('/login');
    }

    #[Test]
    public function store_validates_member_belongs_to_division()
    {
        $srLdr         = $this->createSeniorLeader();
        $division      = $srLdr->member->division;
        $otherDivision = $this->createActiveDivision();
        $member        = $this->createMember(['division_id' => $otherDivision->id]);

        $response = $this->actingAs($srLdr)
            ->post(route('leave.store', $division->slug), [
                'member_id' => $member->id,
                'end_date'  => now()->addMonth()->format('Y-m-d'),
                'reason'    => 'Test reason',
            ]);

        $response->assertSessionHasErrors('member_id');
    }

    #[Test]
    public function store_is_forbidden_for_member_role()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $response = $this->actingAs($user)
            ->post(route('leave.store', $division->slug), [
                'member_id' => $user->member->id,
                'end_date'  => now()->addMonth()->format('Y-m-d'),
                'reason'    => 'Test reason',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function officer_role_is_authorized_to_create_leave()
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createMemberWithUser(
            ['division_id' => $division->id],
            ['role' => Role::OFFICER]
        );

        $this->actingAs($officer);

        $this->assertTrue($officer->can('create', Leave::class));
    }

    #[Test]
    public function officer_can_create_leave_for_another_member()
    {
        $division = $this->createActiveDivision();
        $officer  = $this->createMemberWithUser(
            ['division_id' => $division->id],
            ['role' => Role::OFFICER]
        );
        $member = $this->createMember(['division_id' => $division->id]);

        $response = $this->actingAs($officer)
            ->post(route('leave.store', $division->slug), [
                'member_id'  => $member->id,
                'end_date'   => now()->addMonth()->format('Y-m-d'),
                'leave_type' => 'other',
                'note_body'  => 'Test reason',
            ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('leaves', ['member_id' => $member->id, 'reason' => 'other']);
    }
}
