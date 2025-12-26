<?php

namespace Tests\Unit\Models;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class UserTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    public function test_creating_user_sets_default_settings()
    {
        $user = User::factory()->create();

        $this->assertIsArray($user->settings);
        $this->assertArrayHasKey('snow', $user->settings);
        $this->assertArrayHasKey('ticket_notifications', $user->settings);
    }

    public function test_settings_accessor_merges_with_defaults()
    {
        $user = User::factory()->create();
        $user->forceFill(['settings' => ['custom_key' => 'value']])->save();
        $user->refresh();

        $this->assertArrayHasKey('snow', $user->settings);
        $this->assertArrayHasKey('custom_key', $user->settings);
        $this->assertEquals('value', $user->settings['custom_key']);
    }

    public function test_name_accessor_capitalizes_first_letter()
    {
        $user = User::factory()->create(['name' => 'testuser']);

        $this->assertEquals('Testuser', $user->name);
    }

    public function test_is_role_with_string_returns_correct_value()
    {
        $adminUser = $this->createAdmin();
        $officerUser = $this->createOfficer();

        $this->assertTrue($adminUser->isRole('admin'));
        $this->assertFalse($adminUser->isRole('officer'));
        $this->assertTrue($officerUser->isRole('officer'));
    }

    public function test_is_role_with_array_returns_correct_value()
    {
        $adminUser = $this->createAdmin();

        $this->assertTrue($adminUser->isRole(['admin', 'sr_ldr']));
        $this->assertFalse($adminUser->isRole(['officer', 'jr_ldr']));
    }

    public function test_is_member_returns_true_for_member_position()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::MEMBER,
        ]);

        $this->assertTrue($user->isMember());
    }

    public function test_is_member_returns_false_for_leader_position()
    {
        $division = $this->createActiveDivision();
        $user = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::SQUAD_LEADER,
        ]);

        $this->assertFalse($user->isMember());
    }

    public function test_is_squad_leader_returns_correct_value()
    {
        $division = $this->createActiveDivision();
        $squadLeaderUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::SQUAD_LEADER,
        ]);
        $memberUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::MEMBER,
        ]);

        $this->assertTrue($squadLeaderUser->isSquadLeader());
        $this->assertFalse($memberUser->isSquadLeader());
    }

    public function test_is_platoon_leader_returns_correct_value()
    {
        $division = $this->createActiveDivision();
        $platoonLeaderUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::PLATOON_LEADER,
        ]);
        $memberUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::MEMBER,
        ]);

        $this->assertTrue($platoonLeaderUser->isPlatoonLeader());
        $this->assertFalse($memberUser->isPlatoonLeader());
    }

    public function test_is_division_leader_returns_true_for_co_or_xo()
    {
        $division = $this->createActiveDivision();
        $coUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::COMMANDING_OFFICER,
        ]);
        $xoUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::EXECUTIVE_OFFICER,
        ]);

        $this->assertTrue($coUser->isDivisionLeader());
        $this->assertTrue($xoUser->isDivisionLeader());
    }

    public function test_is_division_leader_returns_false_for_other_positions()
    {
        $division = $this->createActiveDivision();
        $memberUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'position' => Position::PLATOON_LEADER,
        ]);

        $this->assertFalse($memberUser->isDivisionLeader());
    }

    public function test_is_developer_returns_correct_value()
    {
        $adminUser = $this->createAdmin();
        $regularUser = $this->createMemberWithUser([], ['developer' => false]);

        $this->assertTrue($adminUser->isDeveloper());
        $this->assertFalse($regularUser->isDeveloper());
    }

    public function test_can_remove_users_returns_true_for_sergeant_or_higher()
    {
        $division = $this->createActiveDivision();
        $sergeantUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'rank' => Rank::SERGEANT,
        ]);
        $corporalUser = $this->createMemberWithUser([
            'division_id' => $division->id,
            'rank' => Rank::CORPORAL,
        ]);

        $this->assertTrue($sergeantUser->canRemoveUsers());
        $this->assertFalse($corporalUser->canRemoveUsers());
    }

    public function test_assign_role_with_role_model()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $adminRole = Role::whereName('admin')->first();

        $user->assignRole($adminRole);
        $user->refresh();

        $this->assertEquals($adminRole->id, $user->role_id);
    }

    public function test_assign_role_with_string()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $user->assignRole('admin');
        $user->refresh();

        $this->assertEquals('admin', $user->role->name);
    }

    public function test_assign_role_with_integer()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $adminRole = Role::whereName('admin')->first();

        $user->assignRole($adminRole->id);
        $user->refresh();

        $this->assertEquals($adminRole->id, $user->role_id);
    }

    public function test_scope_admins_returns_only_admin_users()
    {
        $adminUser = $this->createAdmin();
        $regularUser = $this->createMemberWithUser();

        $results = User::admins()->get();

        $this->assertTrue($results->contains($adminUser));
        $this->assertFalse($results->contains($regularUser));
    }
}
