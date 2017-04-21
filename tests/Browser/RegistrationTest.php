<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\CreatesApplication;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use CreatesApplication;

    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'AOD_Guybrush')
                ->type('email', 'example@example.com')
                ->type('password', 'example')
                ->type('password_confirmation', 'example')
                ->press('Register')
                ->assertSee('Do not include "AOD_"');
        });
    }
}
