<?php

namespace App\Authorization;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\AbilityAudit;
use App\Models\RoleAbility;
use App\Models\User;
use App\Models\UserAbility;
use Illuminate\Support\Facades\DB;

class PermissionManager
{
    public const ROLE_GRANTED = 'role_granted';

    public const ROLE_REVOKED = 'role_revoked';

    public const USER_GRANTED = 'user_granted';

    public const USER_REVOKED = 'user_revoked';

    public function __construct(private readonly AbilityMap $map) {}

    public function abilitiesFor(Role $role): array
    {
        $current = RoleAbility::where('role', $role)->pluck('ability')->all();

        return array_values(array_filter(Ability::cases(), fn (Ability $ability) => in_array($ability->value, $current, true)));
    }

    public function defaultsFor(Role $role): array
    {
        $defaults = new CodeAbilityMap(new ForumRoleSource);

        return array_values(array_filter(Ability::cases(), fn (Ability $ability) => in_array($role, $defaults->rolesFor($ability), true)));
    }

    public function setRoleAbilities(Role $role, array $abilities, User $actor, string $reason): int
    {
        if ($role === Role::ADMIN && ! in_array(Ability::ManagePermissions, $abilities, true)) {
            $abilities[] = Ability::ManagePermissions;
        }

        $wanted  = array_map(fn (Ability $ability) => $ability->value, $abilities);
        $current = RoleAbility::where('role', $role)->pluck('ability')->all();
        $known   = array_map(fn (Ability $ability) => $ability->value, Ability::cases());
        $added   = array_values(array_diff($wanted, $current));
        $removed = array_values(array_intersect(array_diff($current, $wanted), $known));

        if ($added === [] && $removed === []) {
            return 0;
        }

        DB::transaction(function () use ($role, $added, $removed, $actor, $reason) {
            foreach ($added as $ability) {
                RoleAbility::create(['role' => $role, 'ability' => $ability]);
                $this->audit(self::ROLE_GRANTED, $actor, $reason, ability: $ability, role: $role);
            }

            if ($removed !== []) {
                RoleAbility::where('role', $role)->whereIn('ability', $removed)->delete();

                foreach ($removed as $ability) {
                    $this->audit(self::ROLE_REVOKED, $actor, $reason, ability: $ability, role: $role);
                }
            }
        });

        $this->flush();

        return count($added) + count($removed);
    }

    public function resetRole(Role $role, User $actor, string $reason): int
    {
        return $this->setRoleAbilities($role, $this->defaultsFor($role), $actor, $reason);
    }

    public function grant(User $user, Ability $ability, User $actor, string $reason): UserAbility
    {
        $grant = DB::transaction(function () use ($user, $ability, $actor, $reason) {
            $grant = UserAbility::firstOrCreate(
                ['user_id' => $user->id, 'ability' => $ability->value],
                ['granted_by' => $actor->id, 'reason' => $reason],
            );

            if ($grant->wasRecentlyCreated) {
                $this->audit(self::USER_GRANTED, $actor, $reason, ability: $ability->value, user: $user);
            }

            return $grant;
        });

        $this->flush();

        return $grant;
    }

    public function revoke(UserAbility $grant, User $actor, string $reason): void
    {
        DB::transaction(function () use ($grant, $actor, $reason) {
            $grant->delete();
            $this->audit(self::USER_REVOKED, $actor, $reason, ability: $grant->ability, user: $grant->user);
        });

        $this->flush();
    }

    private function audit(string $action, User $actor, string $reason, string $ability, ?Role $role = null, ?User $user = null): void
    {
        AbilityAudit::create([
            'actor_id' => $actor->id,
            'action'   => $action,
            'role'     => $role,
            'user_id'  => $user?->id,
            'ability'  => $ability,
            'reason'   => $reason,
        ]);
    }

    private function flush(): void
    {
        if ($this->map instanceof DatabaseAbilityMap) {
            $this->map->flush();
        }
    }
}
