<?php

namespace App\Settings;

use App\Models\User;

class UserSettings
{
    use Settable;

    protected $user;

    /**
     * Settings constructor.
     */
    public function __construct(array $settings, User $user)
    {
        $this->settings = $settings;
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    protected function persist()
    {
        return $this->user->update(['settings' => $this->settings]);
    }
}
