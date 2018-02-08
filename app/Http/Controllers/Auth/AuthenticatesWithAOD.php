<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;

trait AuthenticatesWithAOD
{

    public $email;

    public $roles = [];

    /**
     * Authenticates with AOD Community Service
     *
     * @param $request
     * @return bool
     */
    private function validatesCredentials($request)
    {
        $password = md5($request->password);
        $query = "CALL check_user('{$request->username}', '{$password}')";
        $results = DB::connection('aod_forums')->select($query);

        if ( ! empty($results)) {
            $member = array_first($results);

            $this->email = $member->email;
            // TODO: handle roles and perms...
            $this->roles = array_merge(
                explode(',', $member->membergroupids),
                [$member->usergroupid]
            );

            return ($member->valid);
        }

        return false;
    }

}