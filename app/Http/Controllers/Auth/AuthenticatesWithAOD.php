<?php

namespace App\Http\Controllers\Auth;

use Exception;
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

        try {
            $query = "CALL check_user('{$request->username}', '{$password}')";
            $results = DB::connection('aod_forums')->select($query);
        } catch (Exception $exception) {
            return false;
        }

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