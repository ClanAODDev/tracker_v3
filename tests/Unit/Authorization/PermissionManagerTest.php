<?php

namespace Tests\Unit\Authorization;

use App\Authorization\PermissionManager;
use App\Enums\Ability;
use App\Enums\Role;
use App\Models\AbilityAudit;
use App\Models\User;
use App\Models\UserAbility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actor = User::factory()->admin()->create();
    }

    #[Test]
    public function setting_a_roles_abilities_applies_and_audits_each_change(): void
    {
        $manager  = $this->app->make(PermissionManager::class);
        $officer  = User::factory()->officer()->create();
        $wanted   = array_values(array_filter($manager->abilitiesFor(Role::OFFICER), fn ($a) => $a !== Ability::ViewRankActions));
        $wanted[] = Ability::ApproveAwards;

        $changes = $manager->setRoleAbilities(Role::OFFICER, $wanted, $this->actor, 'Officers approve awards now');

        $this->assertSame(2, $changes);
        $this->assertFalse(Gate::forUser($officer)->allows(Ability::ViewRankActions));
        $this->assertTrue(Gate::forUser($officer)->allows(Ability::ApproveAwards));
        $this->assertEqualsCanonicalizing(
            [[PermissionManager::ROLE_REVOKED, Ability::ViewRankActions->value], [PermissionManager::ROLE_GRANTED, Ability::ApproveAwards->value]],
            AbilityAudit::all()->map(fn ($audit) => [$audit->action, $audit->ability])->all(),
        );
        $this->assertTrue(AbilityAudit::all()->every(fn ($audit) => $audit->actor_id === $this->actor->id
            && $audit->role === Role::OFFICER
            && $audit->reason === 'Officers approve awards now'));
    }

    #[Test]
    public function saving_without_changes_writes_nothing(): void
    {
        $manager = $this->app->make(PermissionManager::class);

        $this->assertSame(0, $manager->setRoleAbilities(Role::OFFICER, $manager->abilitiesFor(Role::OFFICER), $this->actor, 'no-op'));
        $this->assertSame(0, AbilityAudit::count());
    }

    #[Test]
    public function the_admin_role_cannot_lose_manage_permissions(): void
    {
        $manager = $this->app->make(PermissionManager::class);

        $manager->setRoleAbilities(Role::ADMIN, [], $this->actor, 'strip everything');

        $this->assertSame([Ability::ManagePermissions], $manager->abilitiesFor(Role::ADMIN));
        $this->assertTrue(Gate::forUser($this->actor)->allows(Ability::ManagePermissions));
    }

    #[Test]
    public function resetting_a_role_restores_the_defaults(): void
    {
        $manager = $this->app->make(PermissionManager::class);
        $manager->setRoleAbilities(Role::SENIOR_LEADER, [], $this->actor, 'strip');

        $manager->resetRole(Role::SENIOR_LEADER, $this->actor, 'back to defaults');

        $this->assertEquals($manager->defaultsFor(Role::SENIOR_LEADER), $manager->abilitiesFor(Role::SENIOR_LEADER));
    }

    #[Test]
    public function granting_and_revoking_a_user_ability_is_audited(): void
    {
        $manager = $this->app->make(PermissionManager::class);
        $member  = User::factory()->create(['role' => Role::MEMBER]);

        $grant = $manager->grant($member, Ability::Recruit, $this->actor, 'Helping with recruitment drive');
        $manager->grant($member, Ability::Recruit, $this->actor, 'duplicate');

        $this->assertTrue(Gate::forUser($member)->allows(Ability::Recruit));
        $this->assertSame(1, UserAbility::count());
        $this->assertSame($this->actor->id, $grant->granted_by);

        $manager->revoke($grant, $this->actor, 'Drive finished');

        $this->assertFalse(Gate::forUser($member)->allows(Ability::Recruit));
        $this->assertSame(
            [[PermissionManager::USER_GRANTED, 'Helping with recruitment drive'], [PermissionManager::USER_REVOKED, 'Drive finished']],
            AbilityAudit::orderBy('id')->get()->map(fn ($audit) => [$audit->action, $audit->reason])->all(),
        );
        $this->assertTrue(AbilityAudit::all()->every(fn ($audit) => $audit->user_id === $member->id));
    }
}
