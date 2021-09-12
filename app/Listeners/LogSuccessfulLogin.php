<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        $event->user->last_login_at = date('Y-m-d H:i:s');
        $event->user->save();
    }
}
