<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

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
            \Auth::login(User::whereName('Guybrush')->first());
        }

        if (\Auth::guest()) {

            $sessionData = $this->getAODSession();

            if ( ! property_exists($sessionData, 'loggedin')) {
                return redirect()->guest('login');
            }

            if ( ! in_array($sessionData->loggedin, [1, 2])) {
                return redirect()->guest('login');
            }

            if ($member = \App\Member::whereClanId($sessionData->userid)) {
                $user = $this->registerNewUser(
                    $sessionData->username,
                    $sessionData->email,
                    $sessionData->userid
                );
            }

            \Auth::login($user);
        }

        return $next($request);
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
     */
    public function registerNewUser($username, $email, $clanId)
    {
        if ($authUser = User::whereClanId($shibUid)->first()) {
            $this->handleRoleAssignment($authUser);
            return $authUser;
        }

        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->member_id = $clanId;
        $user->save();
    }

    public function something($session)
    {
        $username = str_replace('aod_', '', strtolower($session->username));

        if ($user = User::whereName($username)->first()) {
            \Auth::login($user);

            return redirect()->intended();
        }

        if ($this->registerNewUser($username)) {
            return redirect()->intended();
        }
    }
}
