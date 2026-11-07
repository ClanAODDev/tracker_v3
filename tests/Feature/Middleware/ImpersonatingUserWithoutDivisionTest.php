<?php

namespace Tests\Feature\Middleware;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ImpersonatingUserWithoutDivisionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function an_admin_impersonating_a_user_without_a_division_is_returned_to_their_own_account(): void
    {
        $division = $this->createActiveDivision();
        $admin    = $this->createMemberWithUser(['division_id' => $division->id], ['role' => Role::ADMIN]);
        $target   = $this->createMemberWithUser(['division_id' => $division->id]);
        $target->member->update(['division_id' => 0]);

        $this->actingAs($target)
            ->withSession(['impersonating' => true, 'impersonatingUser' => $admin->id])
            ->get(route('home'))
            ->assertRedirect('/');

        $this->assertTrue(auth()->user()->is($admin));
        $this->assertNull(session('impersonating'));
    }

    #[Test]
    public function a_user_without_a_division_who_is_not_impersonated_still_gets_408(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $user->member->update(['division_id' => 0]);

        $this->actingAs($user)->get(route('home'))->assertStatus(408);
    }
}
