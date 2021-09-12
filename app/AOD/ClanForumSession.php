<?php

namespace App\AOD;

use App\AOD\Traits\Procedureable;
use App\Models\Member;
use App\Models\User;
use Auth;
use Exception;

class ClanForumSession
{
    use Procedureable;

    /**
     * Key containing AOD forum session data.
     *
     * @var string
     */
    public $sessionKey = 'aod_sessionhash';

    public function exists()
    {
        if (!User::exists()) {
            throw new \Exception('No users exist. Have you created an account?');
        }

        if ('local' === app()->environment()) {
            $user_id = config('dev_default_user') ?? 1;
            Auth::login(
                config('dev_default_user')
                ? User::find($user_id)
                : User::first()
            );

            return true;
        }

        if (Auth::guest()) {
            $sessionData = $this->getAODSession();

            if (!\is_object($sessionData) || !property_exists($sessionData, 'loggedin')) {
                return false;
            }

            if (!\in_array($sessionData->loggedin, [1, 2], true)) {
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

            (new ClanForumPermissions())->handleAccountRoles($member->clan_id, array_merge(
                array_map('intval', explode(',', $sessionData->membergroupids)),
                [$sessionData->usergroupid]
            ));

            return true;
        }
    }

    /**
     * @param $username
     * @param $email
     * @param $clanId
     *
     * @return User||void
     */
    public function registerNewUser($username, $email, $clanId)
    {
        if ($authUser = User::whereName($username)->first()) {
            return $authUser;
        }

        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->member_id = $clanId;
        $user->save();

        return $user;
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
                'session' => $_COOKIE[$this->sessionKey],
            ]);
        } catch (Exception $exception) {
            return false;
        }
    }
}
