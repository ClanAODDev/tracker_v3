<?php

namespace App\Http\Controllers\Auth;

use App\Services\AODForumService;
use Illuminate\Http\Request;

trait AuthenticatesWithAOD
{
    protected ?string $email = null;

    protected ?int $clanId = null;

    protected array $roles = [];

    protected function validatesCredentials(Request $request): bool
    {
        $result = app(AODForumService::class)->authenticate(
            $request->input('username'),
            $request->input('password')
        );

        if (! $result) {
            return false;
        }

        $this->clanId = $result['clan_id'];
        $this->email  = $result['email'];
        $this->roles  = $result['roles'];

        return true;
    }
}
