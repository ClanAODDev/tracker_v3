<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_remember_cookie_restores_a_passwordless_user_after_the_session_expires(): void
    {
        $user = User::factory()->create();

        Auth::login(user: $user, remember: true);

        $cookie = Cookie::queued(Auth::guard()->getRecallerName());

        Session::flush();
        Auth::forgetGuards();

        $this->withCredentials()
            ->withCookie($cookie->getName(), $cookie->getValue())
            ->getJson(route('session.keep-alive'))
            ->assertOk();

        $this->assertTrue(Auth::viaRemember());
        $this->assertTrue(Auth::user()->is($user));
    }

    #[Test]
    public function a_remember_cookie_issued_before_the_password_override_is_still_accepted(): void
    {
        $user = User::factory()->create(['remember_token' => 'legacy-token']);

        $legacyHash = Auth::guard()->hashPasswordForCookie('');

        $this->withCredentials()
            ->withCookie(Auth::guard()->getRecallerName(), "{$user->id}|legacy-token|{$legacyHash}")
            ->getJson(route('session.keep-alive'))
            ->assertOk();

        $this->assertTrue(Auth::user()->is($user));
    }
}
