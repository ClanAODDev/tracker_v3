<?php

namespace App\Http\Middleware;

use App\User;
use Auth;
use Closure;
use Exception;

class ClanForumAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->environment() === 'local') {
            Auth::login(User::whereName('Guybrush')->first());
        }

        if (Auth::guest()) {

            $sessionData = $this->getAODSession();

            if ( ! is_object($sessionData)) {
                return redirect()->guest('login');
            }

            if ( ! in_array($sessionData->loggedin, [1, 2])) {
                return false;
            }

            $username = str_replace('aod_', '', strtolower($sessionData->username));

            if ($member = \App\Member::whereClanId($sessionData->userid)->first()) {

                $user = $this->registerNewUser(
                    $username,
                    $sessionData->email,
                    $sessionData->userid
                );
            }

            Auth::login($user);
        }

        return redirect()->intended();
    }

    /**
     * @return bool
     */
    private function getAODSession()
    {
        if ( ! isset($_COOKIE['aod_sessionhash'])) {
            return false;
        }

        $data = $this->callStoredProcedure($_COOKIE['aod_sessionhash']);

        return $data ?? false;
    }

    /**
     * @param $aod_sessionhash
     * @return bool|null
     */
    private function callStoredProcedure($aod_sessionhash)
    {
        try {
            $results = \DB::connection('aod_forums')
                ->select("CALL check_session('{$aod_sessionhash}')");

            return $results[0] ?? false;

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
    }
}