<?php

namespace App\AOD;

use App\Member;
use App\User;
use Auth;
use DB;
use Exception;

class ClanForumSession
{
    /**
     * Stored procedure that validates forum session
     *
     * @var string
     */
    public $storedProcedure = 'check_session';

    /**
     * Key containing AOD forum session data
     * @var string
     */
    public $sessionKey = 'aod_sessionhash';

    public function exists()
    {
        if (app()->environment() === 'local') {
            Auth::login(User::whereName('Guybrush')->first());
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

        $data = $this->callStoredProcedure($_COOKIE[$this->sessionKey]);

        return $data ?? false;
    }

    /**
     * @param $aod_session
     * @return bool|null
     */
    private function callStoredProcedure($aod_session)
    {
        try {
            $results = DB::connection('aod_forums')
                ->select("CALL {$this->storedProcedure}('{$aod_session}')");

            return array_key_exists(0, $results)
                ? $results[0]
                : null;
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
