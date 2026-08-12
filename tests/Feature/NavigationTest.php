<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class NavigationTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function developer_with_member_role_sees_admin_nav_links()
    {
        $division             = $this->createActiveDivision();
        $developer            = $this->createMemberWithUser(['division_id' => $division->id]);
        $developer->developer = true;
        $developer->save();

        $this->actingAs($developer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('/admin', false)
            ->assertSee('/operations', false)
            ->assertSee('Log Viewer');
    }

    #[Test]
    public function regular_member_does_not_see_admin_nav_links()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('/admin', false)
            ->assertDontSee('/operations', false)
            ->assertDontSee('Log Viewer');
    }

    #[Test]
    public function developer_with_member_role_gets_bulk_mode_enabled()
    {
        $division             = $this->createActiveDivision();
        $developer            = $this->createMemberWithUser(['division_id' => $division->id]);
        $developer->developer = true;
        $developer->save();

        $this->actingAs($developer)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('"canUseBulkMode":true', false);
    }

    #[Test]
    public function regular_member_does_not_get_bulk_mode_enabled()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('"canUseBulkMode":false', false);
    }
}
