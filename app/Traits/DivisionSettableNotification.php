<?php

namespace App\Traits;

use App\Models\Division;

trait DivisionSettableNotification
{
    public function shouldSend(Division $notifiable): bool
    {
        if (! property_exists($this, 'alertSetting')) {
            throw new \Exception('The $alertSetting property must be defined in ' . static::class);
        }

        return $notifiable->settings()->get($this->alertSetting);
    }
}
