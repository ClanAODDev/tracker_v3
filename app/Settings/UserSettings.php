<?php

namespace App\Settings;

class UserSettings
{

    use Settable;

    protected $user;

    /**
     * Settings constructor.
     * @param array $settings
     * @param User $user
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