<?php

namespace App\AOD;

use App\AOD\Traits\Procedureable;
use App\Member;
use App\User;
use Auth;
use DB;
use Exception;

class ClanForumSession
{

    use Procedureable;

    /**
     * Key containing AOD forum session data
     * @var string
     */
    public $sessionKey = 'aod_sessionhash';

    public function exists()
    {
        if (app()->environment() === 'local') {
            Auth::login(User::whereMemberId(273)->first());
            return true;
        }

        if (Auth::guest()) {
            $sessionData = $this->getAODSession();

            if (!is_object($sessionData)) {
                return false;
            }

            if (!in_array($sessionData->loggedin, [1, 2])) {
                return false;
            }

            $username = str_replace('aod_', '', strtolower($sessionData->username));

            $member = Member::whereClanId($sessionData->userid)->first();

            if (!$member) {
                abort(403, 'Not authorized');
            }

            $user = $this->registerNewUser(
                $username,
                $sessionData->email,
                $member->id
            );

            (new ClanForumPermissions())->handleAccountRoles($member->clan_id, array_merge(
                array_map('intval', explode(',', $sessionData->membergroupids)),
                [$sessionData->usergroupid]
            ));

            Auth::login($user);

            return true;
        }
    }

    /**
     * @return bool
     */
    private function getAODSession()
    {
        if (!isset($_COOKIE[$this->sessionKey])) {
            return false;
        }

        try {
            return $this->callProcedure('check_session', [
                'session' => $_COOKIE[$this->sessionKey]
            ]);
        } catch (Exception $exception) {
            return false;
        }
    }



    /**
     * @param $username
     * @param $email
     * @param $clanId
     * @return User||void
     */
    public function registerNewUser($username, $email, $clanId)
    {
        if ($authUser = User::whereName($username)->first()) {
            return $authUser;
        }

        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->member_id = $clanId;
        $user->save();

        return $user;
    }
}
