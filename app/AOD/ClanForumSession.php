<?php

namespace App\AOD;

use App\AOD\Traits\Procedureable;
use App\Models\Member;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClanForumSession
{
    use Procedureable;

    /**
     * Key containing AOD forum session data.
     *
     * @var string
     */
    public $sessionKey = 'aod_sessionhash';

    public function exists(): bool
    {
        if (! User::exists()) {
            throw new Exception('No users exist. Have you created an account?');
        }

        if (str_contains(app()->environment(), 'local')) {
            $this->loginLocalUser();

            return true;
        }

        if (! Auth::guest()) {
            return true;
        }

        $sessionData = $this->getAODSession();
        if (! $this->isValidSessionData($sessionData)) {
            return false;
        }

        $username = $this->normalizeUsername($sessionData->username);

        $member = Member::whereClanId($sessionData->userid)->first();
        if (! $member) {
            abort(403, 'Not authorized');
        }

        $user = $this->registerNewUser($username, $sessionData->email, $member->id);
        Auth::login($user);

        $memberGroupIds = array_map('intval', explode(',', $sessionData->membergroupids));
        $roles = array_merge($memberGroupIds, [$sessionData->usergroupid]);
        (new ClanForumPermissions)->handleAccountRoles($member->clan_id, $roles);

        return true;
    }

    protected function loginLocalUser(): void
    {
        $userId = config('dev_default_user') ?? 1;
        $user = config('dev_default_user') ? User::find($userId) : User::first();
        Auth::login($user);
    }

    protected function normalizeUsername(string $username): string
    {
        return str_replace('aod_', '', strtolower($username));
    }

    protected function isValidSessionData($sessionData): bool
    {
        if (! is_object($sessionData) || ! property_exists($sessionData, 'loggedin')) {
            return false;
        }

        return in_array($sessionData->loggedin, [1, 2], true);
    }

    /**
     * @return User||void
     */
    public function registerNewUser(string $username, string $email, int $clanId): User
    {
        if ($existingUser = User::whereName($username)->first()) {
            return $existingUser;
        }

        $user = new User;
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
        if (! isset($_COOKIE[$this->sessionKey])) {
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
