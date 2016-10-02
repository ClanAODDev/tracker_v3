<?php

class RegisterTest extends TestCase
{

    /**
     * Ensure username validation functioning
     * AOD_ is not allowed in the username
     */
    public function testAODInUsernameFails()
    {
        $this->visit('/register');
        $this->type('AOD_SgtMaj_Bluntz', 'name');
        $this->type('example@example.com', 'email');
        $this->type('example', 'password');
        $this->type('example', 'password_confirmation');
        $this->press('Register');
        $this->seePageIs('/register');
        $this->see('Username cannot contain \"AOD_\"');
    }

    /**
     * Verify user registration with valid input
     */
    public function testUserRegistration()
    {
        $this->visit('/register');
        $this->type('SgtMaj_Bluntz', 'name');
        $this->type('example@example.com', 'email');
        $this->type('example', 'password');
        $this->type('example', 'password_confirmation');
        $this->press('Register');
        $this->seePageIs('/home');
    }
}