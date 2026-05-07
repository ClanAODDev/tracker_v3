<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class LogViewerAccessTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function developer_can_access_log_viewer()
    {
        $developer = $this->createMemberWithUser([], ['developer' => true]);

        $response = $this->actingAs($developer)
            ->get('/log-viewer');

        $response->assertOk();
    }

    #[Test]
    public function admin_with_developer_flag_can_access_log_viewer()
    {
        $admin = $this->createAdmin([], ['developer' => true]);

        $response = $this->actingAs($admin)
            ->get('/log-viewer');

        $response->assertOk();
    }

    #[Test]
    public function admin_without_developer_flag_cannot_access_log_viewer()
    {
        $admin = $this->createAdmin([], ['developer' => false]);

        $response = $this->actingAs($admin)
            ->get('/log-viewer');

        $response->assertForbidden();
    }

    #[Test]
    public function senior_leader_cannot_access_log_viewer()
    {
        $seniorLeader = $this->createSeniorLeader();

        $response = $this->actingAs($seniorLeader)
            ->get('/log-viewer');

        $response->assertForbidden();
    }

    #[Test]
    public function officer_cannot_access_log_viewer()
    {
        $officer = $this->createOfficer();

        $response = $this->actingAs($officer)
            ->get('/log-viewer');

        $response->assertForbidden();
    }

    #[Test]
    public function regular_member_cannot_access_log_viewer()
    {
        $member = $this->createMemberWithUser();

        $response = $this->actingAs($member)
            ->get('/log-viewer');

        $response->assertForbidden();
    }

    #[Test]
    public function unauthenticated_user_cannot_access_log_viewer()
    {
        $response = $this->get('/log-viewer');

        $response->assertForbidden();
    }
}
