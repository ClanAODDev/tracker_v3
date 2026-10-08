<?php

namespace Tests\Unit\Authorization;

use App\Authorization\AbilityMap;
use App\Authorization\CodeAbilityMap;
use App\Authorization\DatabaseAbilityMap;
use App\Authorization\ForumRoleSource;
use App\Enums\Ability;
use App\Enums\Role;
use App\Models\RoleAbility;
use App\Models\User;
use App\Models\UserAbility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DatabaseAbilityMapTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_seeded_defaults_match_the_code_map_for_every_role_and_impersonation(): void
    {
        $database = $this->app->make(AbilityMap::class);
        $code     = new CodeAbilityMap(new ForumRoleSource);

        $this->assertInstanceOf(DatabaseAbilityMap::class, $database);

        foreach (Role::cases() as $stored) {
            foreach ([null, ...Role::cases()] as $impersonated) {
                session()->forget('impersonatingRole');

                if ($impersonated) {
                    session(['impersonatingRole' => $impersonated->value]);
                }

                $user = (new User)->forceFill(['role' => $stored]);

                foreach (Ability::cases() as $ability) {
                    $this->assertSame(
                        $code->allows($user, $ability),
                        $database->allows($user, $ability),
                        "{$ability->name} differs for {$stored->slug()} viewing as " . ($impersonated?->slug() ?? 'self'),
                    );
                }
            }
        }
    }

    #[Test]
    public function editing_a_roles_abilities_changes_only_that_role(): void
    {
        $map     = $this->app->make(AbilityMap::class);
        $officer = User::factory()->officer()->create();
        $senior  = User::factory()->create(['role' => Role::SENIOR_LEADER]);

        RoleAbility::where('role', Role::OFFICER)->where('ability', Ability::ViewRankActions->value)->delete();
        RoleAbility::create(['role' => Role::OFFICER, 'ability' => Ability::ApproveAwards->value]);
        $map->flush();

        $this->assertFalse($map->allows($officer, Ability::ViewRankActions));
        $this->assertTrue($map->allows($officer, Ability::ApproveAwards));
        $this->assertTrue($map->allows($senior, Ability::ViewRankActions));
    }

    #[Test]
    public function a_user_grant_applies_to_that_user_only(): void
    {
        $map     = $this->app->make(AbilityMap::class);
        $granted = User::factory()->create(['role' => Role::MEMBER]);
        $other   = User::factory()->create(['role' => Role::MEMBER]);

        UserAbility::create(['user_id' => $granted->id, 'ability' => Ability::Recruit->value, 'reason' => 'test']);
        $map->flush();

        $this->assertTrue($map->allows($granted, Ability::Recruit));
        $this->assertTrue(Gate::forUser($granted)->allows(Ability::Recruit));
        $this->assertFalse($map->allows($other, Ability::Recruit));
        $this->assertFalse($map->allows($granted, Ability::ManageMembers));
    }

    #[Test]
    public function admins_always_hold_manage_permissions(): void
    {
        $map   = $this->app->make(AbilityMap::class);
        $admin = User::factory()->admin()->create();

        RoleAbility::where('ability', Ability::ManagePermissions->value)->delete();
        $map->flush();

        $this->assertTrue($map->allows($admin, Ability::ManagePermissions));
        $this->assertFalse($map->roleHas(Role::ADMIN, Ability::ManagePermissions));
    }

    #[Test]
    public function rows_for_abilities_that_no_longer_exist_are_ignored(): void
    {
        $map     = $this->app->make(AbilityMap::class);
        $officer = User::factory()->officer()->create();

        RoleAbility::create(['role' => Role::OFFICER, 'ability' => 'ability:removed_long_ago']);
        UserAbility::create(['user_id' => $officer->id, 'ability' => 'ability:removed_long_ago', 'reason' => 'test']);
        $map->flush();

        $this->assertTrue($map->allows($officer, Ability::ViewRankActions));
    }
}
