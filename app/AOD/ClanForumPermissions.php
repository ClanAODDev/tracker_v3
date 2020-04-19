<?php

namespace App\AOD;

use App\AOD\Traits\Procedureable;

class ClanForumPermissions
{
    use Procedureable;

    /**
     * Provision account role based on forum groups
     * @param $clanForumId
     * @param null $groupIds
     * @return void|null
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
         * Update role unless current role matches new role
         */
        switch (true) {
            case array_intersect($groupIds, ['Banned Users', 49]):
                return ($user->role_id != 6) ? $this->assignRole('banned') : null;
            case array_intersect($groupIds, ['Administrators', 6]):
                return ($user->role_id != 5) ? $this->assignRole('admin') : null;
            case array_intersect($groupIds, ['AOD Sergeants', 52, 'AOD Staff Sergeants', 66]):
                return ($user->role_id != 4) ? $this->assignRole('sr_ldr') : null;
            case array_intersect($groupIds, $officerRoleIds):
                return ($user->role_id != 2) ? $this->assignRole('officer') : null;
            default:
                return ($user->role_id != 1) ? $this->assignRole('member') : null;
        }
    }

    /**
     * @param string $role
     * @return
     */
    private function assignRole(string $role)
    {
        \Log::info("Role {$role} granted to user " . auth()->id());

        return auth()->user()->assignRole($role);
    }
}
