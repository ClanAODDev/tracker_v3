<?php

namespace App\AOD;

use App\User;
use Auth;

class ClanForumSession
{

    protected $sessionKey = 'aod_sessionhash';

    public function exists()
    {
        if (app()->environment() === 'local') {
            Auth::login(User::whereName('Guybrush')->first());
        }

        if (Auth::guest()) {
            $sessionData = $this->getAODSession();

            //dump($sessionData);

            if ( ! is_object($sessionData)) {
                return false;
            }

            if ( ! in_array($sessionData->loggedin, [1, 2])) {
                return false;
            }

            $username = str_replace('aod_', '', strtolower($sessionData->username));

            $member = \App\Member::whereClanId($sessionData->userid)->first();

            if ( ! $member) {
                abort(403, 'Not authorized');
            }

            $user = $this->registerNewUser(
                $username,
                $sessionData->email,
                $member->id
            );

            Auth::login($user);

            return true;
        }
    }

    /**
     * @return bool
     */
    private function getAODSession()
    {
        if ( ! isset($_COOKIE[$this->sessionKey])) {
            return false;
        }

        $data = $this->callStoredProcedure($_COOKIE[$this->sessionKey]);

        return $data ?? false;
    }

    /**
     * @param $aod_sessionhash
     * @return bool|null
     */
    private function callStoredProcedure()
    {
        try {
            $results = \DB::connection('aod_forums')
                ->select("CALL check_session('{$_COOKIE[$this->sessionKey]}')");

            return $results[0];

        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param $username
     * @param $email
     * @param $clanId
     * @return \App\User||void
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

    /**
     * Destroy AOD session hash
     */
    public function destroy()
    {
        $_COOKIE[$this->sessionKey] = null;
    }
}
