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
            /**
             * Banned Users.
             */
            case array_intersect($groupIds, [49]):
                return (6 !== $user->role_id) ? $this->assignRole('banned') : null;
            /**
             * 6 - Administrators.
             */
            case array_intersect($groupIds, [6]):
                return (5 !== $user->role_id) ? $this->assignRole('admin') : null;
            /**
             * 52 - AOD Sergeants
             * 66 - AOD Staff Sergeants
             * 80 - Division CO
             * 79 - Division XO.
             */
            case array_intersect($groupIds, [52, 66, 80, 79]):
                return (4 !== $user->role_id) ? $this->assignRole('sr_ldr') : null;
            /**
             * Division officer usergroup.
             */
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
