<?php

namespace App\Authorization;

use App\Enums\Ability;
use App\Enums\Role;
use App\Models\RoleAbility;
use App\Models\User;
use App\Models\UserAbility;

class DatabaseAbilityMap implements AbilityMap
{
    private ?array $roleAbilities = null;

    private array $userAbilities = [];

    public function __construct(private readonly RoleSource $roles) {}

    public function allows(User $user, Ability $ability): bool
    {
        if ($this->grantedTo($user, $ability)) {
            return true;
        }

        $role = $ability->resolvesAgainstStoredRole()
            ? $this->roles->storedRole($user)
            : $this->roles->effectiveRole($user);

        if ($role === null) {
            return false;
        }

        if ($role === Role::ADMIN && $ability === Ability::ManagePermissions) {
            return true;
        }

        return isset($this->roleAbilities()[$role->value][$ability->value]);
    }

    public function roleHas(Role $role, Ability $ability): bool
    {
        return isset($this->roleAbilities()[$role->value][$ability->value]);
    }

    public function flush(): void
    {
        $this->roleAbilities = null;
        $this->userAbilities = [];
    }

    private function grantedTo(User $user, Ability $ability): bool
    {
        $userId = $user->getKey();

        if ($userId === null) {
            return false;
        }

        $this->userAbilities[$userId] ??= UserAbility::where('user_id', $userId)->pluck('ability')->flip()->all();

        return isset($this->userAbilities[$userId][$ability->value]);
    }

    private function roleAbilities(): array
    {
        return $this->roleAbilities ??= RoleAbility::query()
            ->get(['role', 'ability'])
            ->groupBy(fn (RoleAbility $row) => $row->role->value)
            ->map(fn ($rows) => $rows->pluck('ability')->flip()->all())
            ->all();
    }
}
