<?php

namespace App\Traits;

use App\Models\Division;
use Exception;

trait DivisionSettableNotification
{
    public function shouldSend(Division $notifiable): bool
    {
        if (! property_exists($this, 'alertSetting')) {
            throw new Exception('The $alertSetting property must be defined in ' . static::class);
        }

        return (bool) $notifiable->settings()->get($this->alertSetting);
    }
}
