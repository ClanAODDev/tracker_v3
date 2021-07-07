<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected bool $seed = true;

    protected function signIn($user = null)
    {
        $user = $user ?: create(\App\Models\User::class);

        $this->actingAs($user);

        return $this;
    }
}
