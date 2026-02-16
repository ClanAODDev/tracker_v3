<?php

namespace App\AOD;

use App\Enums\ForumGroup;
use App\Enums\Role;
use App\Services\ForumProcedureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClanForumPermissions
{
    public function __construct(
        protected ForumProcedureService $procedureService
    ) {}

    public function handleAccountRoles(int $clanForumId, ?array $groupIds = null): void
    {
        if (empty($groupIds)) {
            $data = $this->procedureService->getUser($clanForumId);

            if (! $data) {
                return;
            }

            $groupIds = array_merge([$data->usergroupid], explode(',', $data->membergroupids));
        }

        $user = auth()->user();
        $user->member()->update(['groups' => $groupIds]);

        $officerRoleIds = DB::table('divisions')
            ->select('officer_role_id')
            ->where('active', true)
            ->whereNotNull('officer_role_id')
            ->where('officer_role_id', '!=', 0)
            ->pluck('officer_role_id')
            ->toArray();

        $newRole = match (true) {
            $this->inGroup($groupIds, [ForumGroup::BANNED->value])      => Role::BANNED,
            $this->inGroup($groupIds, [ForumGroup::ADMIN->value])       => Role::ADMIN,
            $this->inGroup($groupIds, ForumGroup::seniorLeaderGroups()) => Role::SENIOR_LEADER,
            $this->inGroup($groupIds, $officerRoleIds)                  => Role::OFFICER,
            default                                                     => Role::MEMBER,
        };

        if ($user->role !== $newRole) {
            $this->assignRole($newRole);
        }
    }

    private function inGroup(array $userGroups, array $targetGroups): bool
    {
        return ! empty(array_intersect($userGroups, $targetGroups));
    }

    private function assignRole(Role $role): void
    {
        $user = auth()->user();

        Log::info("Role {$role->slug()} granted to {$user->name} ({$user->id})");

        $user->assignRole($role);
    }
}
