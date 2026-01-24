<?php

namespace App\AOD;

use App\Models\Member;
use App\Models\User;
use App\Services\ForumProcedureService;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClanForumSession
{
    protected string $sessionKey = 'aod_sessionhash';

    public function __construct(
        protected ForumProcedureService $procedureService
    ) {}

    public function exists(): bool
    {
        if (! User::exists()) {
            throw new Exception('No users exist. Have you created an account?');
        }

        if ($this->isLocalEnvironment()) {
            return $this->handleLocalEnvironment();
        }

        if (Auth::check()) {
            return true;
        }

        return $this->authenticateFromForumSession();
    }

    protected function isLocalEnvironment(): bool
    {
        return str_contains(app()->environment(), 'local');
    }

    protected function handleLocalEnvironment(): bool
    {
        if (request()->cookie('local_logged_out')) {
            return false;
        }

        $this->loginLocalUser();

        return true;
    }

    protected function loginLocalUser(): void
    {
        $userId = config('dev_default_user') ?? 1;
        $user = config('dev_default_user') ? User::find($userId) : User::first();
        Auth::login($user);
    }

    protected function authenticateFromForumSession(): bool
    {
        $sessionHash = request()->cookie($this->sessionKey);
        if (! $sessionHash) {
            return false;
        }

        $sessionData = $this->procedureService->checkSession($sessionHash);
        if (! $this->isValidSessionData($sessionData)) {
            return false;
        }

        $member = Member::whereClanId($sessionData->userid)->first();
        if (! $member) {
            abort(403, 'Not authorized');
        }

        $user = $this->findOrCreateUser($sessionData, $member);
        Auth::login($user);

        $this->syncForumPermissions($member->clan_id, $sessionData);

        return true;
    }

    protected function isValidSessionData($sessionData): bool
    {
        if (! is_object($sessionData) || ! property_exists($sessionData, 'loggedin')) {
            return false;
        }

        return in_array($sessionData->loggedin, [1, 2], true);
    }

    protected function findOrCreateUser(object $sessionData, Member $member): User
    {
        $username = $this->normalizeUsername($sessionData->username);

        if ($existingUser = User::whereName($username)->first()) {
            return $existingUser;
        }

        $user = new User;
        $user->name = $username;
        $user->email = $sessionData->email;
        $user->member_id = $member->clan_id;
        $user->save();

        return $user;
    }

    protected function normalizeUsername(string $username): string
    {
        return str_replace('aod_', '', strtolower($username));
    }

    protected function syncForumPermissions(int $clanId, object $sessionData): void
    {
        $memberGroupIds = array_map('intval', explode(',', $sessionData->membergroupids));
        $roles = array_merge($memberGroupIds, [$sessionData->usergroupid]);

        app(ClanForumPermissions::class)->handleAccountRoles($clanId, $roles);
    }
}
