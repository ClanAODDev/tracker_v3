<?php

namespace App\AOD;

use App\AOD\Traits\Procedureable;

class ClanForumPermissions
{
    use Procedureable;

    /**
     * Provision account role based on forum groups.
     *
     * @param $clanForumId
     * @param null $groupIds
     *
     * @return null|void
     */
    public function handleAccountRoles($clanForumId, $groupIds = null)
    {
        if (empty($groupIds)) {
            $data = $this->callProcedure('get_user', $clanForumId);

            $groupIds = array_merge([$data->usergroupid], explode(',', $data->membergroupids));
        }

        $user = auth()->user();
        $user->member()->update(['groups' => $groupIds]);

        $officerRoleIds = \DB::table('divisions')->select('officer_role_id')
            ->where('active', true)
            ->where('officer_role_id', '!=', null)
            ->pluck('officer_role_id')->toArray();

        /**
         * Update role unless current role matches new role.
         */
        switch (true) {
            case array_intersect($groupIds, ['Banned Users', 49]):
                return (6 !== $user->role_id) ? $this->assignRole('banned') : null;

            case array_intersect($groupIds, ['Administrators', 6]):
                return (5 !== $user->role_id) ? $this->assignRole('admin') : null;

            case array_intersect($groupIds, ['AOD Sergeants', 52, 'AOD Staff Sergeants', 66]):
                return (4 !== $user->role_id) ? $this->assignRole('sr_ldr') : null;

            case array_intersect($groupIds, $officerRoleIds):
                return (2 !== $user->role_id) ? $this->assignRole('officer') : null;

            default:
                return (1 !== $user->role_id) ? $this->assignRole('member') : null;
        }
    }

    /**
     * @return
     */
    private function assignRole(string $role)
    {
        \Log::info("Role {$role} granted to user " . auth()->id());

        return auth()->user()->assignRole($role);
    }
}
