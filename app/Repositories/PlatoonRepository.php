<?php

namespace App\Repositories;

use App\Models\Platoon;
use App\Traits\HasActivityGraph;

class PlatoonRepository
{
    use HasActivityGraph;

    public function getPlatoonVoiceActivity(Platoon $platoon): array
    {
        return $this->getActivity('last_voice_activity', $platoon);
    }
}
