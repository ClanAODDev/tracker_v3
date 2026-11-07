<?php

namespace App\Models;

use App\Enums\ApiScope;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    public function can($ability)
    {
        if (! parent::can($ability)) {
            return false;
        }

        $owner = $this->tokenable;

        if (! $owner instanceof User) {
            return false;
        }

        $mintable = array_map(fn (ApiScope $scope) => $scope->value, ApiScope::availableFor($owner));

        return in_array($ability, $mintable, true);
    }
}
