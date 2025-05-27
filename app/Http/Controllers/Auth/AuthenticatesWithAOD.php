<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait AuthenticatesWithAOD
{
    protected ?string $email = null;

    protected ?int $clanId = null;

    protected array $roles = [];

    protected function setMemberAttributes(object $member): void
    {
        $this->clanId = (int) $member->userid;
        $this->email = $member->email;
        $this->roles = array_merge(
            array_map('intval', explode(',', $member->membergroupids)),
            [(int) $member->usergroupid]
        );
    }

    /**
     * Authenticates with AOD Community Service.
     */
    protected function validatesCredentials(Request $request): bool
    {
        try {
            $results = DB::connection('aod_forums')
                ->select(
                    'CALL check_user(:username, :password)',
                    [
                        'username' => $request->input('username'),
                        'password' => md5($request->input('password')),
                    ]
                );
        } catch (Exception $exception) {
            Log::error('AOD Authentication failed: ' . $exception->getMessage());

            return false;
        }

        if (! empty($results)) {

            $member = Arr::first($results);
            $this->setMemberAttributes($member);

            return ($member->valid ?? false) ? true : false;
        }

        return false;
    }
}
