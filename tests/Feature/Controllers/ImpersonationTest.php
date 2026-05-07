<?php

namespace Tests\Feature\Controllers;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class ImpersonationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function admin_can_impersonate_regular_user()
    {
        $admin   = $this->createAdmin();
        $officer = $this->createOfficer();

        $response = $this->actingAs($admin)
            ->get(route('impersonate', $officer));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($officer);
        $this->assertTrue(session('impersonating'));
    }

    #[Test]
    public function admin_cannot_impersonate_developer()
    {
        $admin     = $this->createAdmin([], ['developer' => false]);
        $developer = $this->createAdmin([], ['developer' => true]);

        $response = $this->actingAs($admin)
            ->get(route('impersonate', $developer));

        $response->assertForbidden();
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function admin_cannot_impersonate_themselves()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('impersonate', $admin));

        $response->assertForbidden();
    }

    #[Test]
    public function officer_cannot_impersonate()
    {
        $officer = $this->createOfficer();
        $member  = $this->createMemberWithUser();

        $response = $this->actingAs($officer)
            ->get(route('impersonate', $member));

        $response->assertForbidden();
    }

    #[Test]
    public function cannot_impersonate_while_already_impersonating()
    {
        $admin    = $this->createAdmin();
        $officer1 = $this->createOfficer();
        $officer2 = $this->createOfficer();

        $this->actingAs($admin)
            ->withSession(['impersonating' => true, 'impersonatingUser' => $admin->id])
            ->get(route('impersonate', $officer2))
            ->assertForbidden();
    }

    #[Test]
    public function developer_can_impersonate_in_local_environment()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $developer      = $this->createAdmin([], ['developer' => true]);
        $otherDeveloper = $this->createAdmin([], ['developer' => true]);

        $response = $this->actingAs($developer)
            ->get(route('impersonate', $otherDeveloper));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($otherDeveloper);
    }

    #[Test]
    public function developer_can_impersonate_in_testing_environment()
    {
        $this->app->detectEnvironment(fn () => 'testing');

        $developer = $this->createAdmin([], ['developer' => true]);
        $officer   = $this->createOfficer();

        $response = $this->actingAs($developer)
            ->get(route('impersonate', $officer));

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($officer);
    }

    #[Test]
    public function developer_cannot_impersonate_in_production()
    {
        $this->app->detectEnvironment(fn () => 'production');

        $developer = $this->createMemberWithUser([], ['developer' => true, 'role' => Role::MEMBER]);
        $officer   = $this->createOfficer();

        $response = $this->actingAs($developer)
            ->get(route('impersonate', $officer));

        $response->assertForbidden();
    }

    #[Test]
    public function end_impersonation_returns_to_original_user()
    {
        $admin   = $this->createAdmin();
        $officer = $this->createOfficer();

        $this->actingAs($officer)
            ->withSession(['impersonating' => true, 'impersonatingUser' => $admin->id])
            ->get(route('end-impersonation'));

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session('impersonating', false));
    }
}
