<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\CreatesApplication;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    use CreatesApplication;

    /** @test */
    public function test_user_cannot_incude_aod_in_username()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'AOD_Guybrush')
                ->type('email', 'example2@example.com')
                ->type('password', 'example')
                ->type('password_confirmation', 'example')
                ->press('Register')
                ->assertSee('Do not include "AOD_"');
        });
    }

    /** @test */
    public function test_user_must_be_AOD_member()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'aNonMemberName__')
                ->type('email', 'example2@example.com')
                ->type('password', 'example')
                ->type('password_confirmation', 'example')
                ->press('Register')
                ->assertSee('AOD member name not found');
        });
    }
}
