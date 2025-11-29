<?php

namespace Tests\Feature\Filament;

use App\Enums\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Tests\FilamentTestCase;

final class PanelAuthorizationTest extends FilamentTestCase
{
    public function test_admin_can_access_admin_panel(): void
    {
        $user = $this->createAdminUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_admin_can_access_mod_panel(): void
    {
        $user = $this->createAdminUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('mod')));
    }

    public function test_admin_can_access_profile_panel(): void
    {
        $user = $this->createAdminUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('profile')));
    }

    public function test_officer_cannot_access_admin_panel(): void
    {
        $user = $this->createOfficerUser();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_officer_can_access_mod_panel(): void
    {
        $user = $this->createOfficerUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('mod')));
    }

    public function test_officer_can_access_profile_panel(): void
    {
        $user = $this->createOfficerUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('profile')));
    }

    public function test_sr_ldr_cannot_access_admin_panel(): void
    {
        $user = User::factory()->state(['role' => Role::SENIOR_LEADER])->create();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_sr_ldr_can_access_mod_panel(): void
    {
        $user = User::factory()->state(['role' => Role::SENIOR_LEADER])->create();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('mod')));
    }

    public function test_sr_ldr_can_access_profile_panel(): void
    {
        $user = User::factory()->state(['role' => Role::SENIOR_LEADER])->create();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('profile')));
    }

    public function test_member_cannot_access_admin_panel(): void
    {
        $user = $this->createMemberUser();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_member_cannot_access_mod_panel(): void
    {
        $user = $this->createMemberUser();

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('mod')));
    }

    public function test_member_can_access_profile_panel(): void
    {
        $user = $this->createMemberUser();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('profile')));
    }

    public function test_developer_can_access_all_panels(): void
    {
        $user = User::factory()->state(['developer' => true])->create();

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('mod')));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('profile')));
    }

    public function test_unauthenticated_user_redirected_from_admin_panel(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_unauthenticated_user_redirected_from_mod_panel(): void
    {
        $this->get('/operations')->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_admin_panel(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin')->assertSuccessful();
    }

    public function test_authenticated_officer_redirected_from_admin_panel(): void
    {
        $this->actingAsOfficer();

        $this->get('/admin')->assertForbidden();
    }

    public function test_authenticated_officer_can_view_mod_panel(): void
    {
        $this->actingAsOfficer();

        $this->get('/operations')->assertSuccessful();
    }

    public function test_authenticated_member_redirected_from_mod_panel(): void
    {
        $this->actingAsMember();

        $this->get('/operations')->assertForbidden();
    }

    public function test_banned_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => Role::BANNED]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_banned_user_cannot_access_mod_panel(): void
    {
        $user = User::factory()->create(['role' => Role::BANNED]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('mod')));
    }

    public function test_banned_user_cannot_access_profile_panel(): void
    {
        $user = User::factory()->create(['role' => Role::BANNED]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('profile')));
    }
}
