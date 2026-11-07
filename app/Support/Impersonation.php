<?php

namespace App\Support;

use App\Models\User;

final class Impersonation
{
    public static function start(User $impersonator, User $target): void
    {
        session(['impersonating' => true, 'impersonatingUser' => $impersonator->id]);

        auth()->login($target);
    }

    public static function stop(): ?User
    {
        if (! session('impersonating') || ! session('impersonatingUser')) {
            return null;
        }

        $original = User::find(session('impersonatingUser'));

        session()->forget(['impersonating', 'impersonatingUser']);

        if ($original) {
            auth()->login($original);
        }

        return $original;
    }
}
