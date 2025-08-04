<?php

namespace App\Repositories;

use App\Models\Squad;
use App\Traits\HasActivityGraph;

class SquadRepository
{
    use HasActivityGraph;

    public function getSquadVoiceActivity(Squad $squad): array
    {
        return $this->getActivity('last_voice_activity', $squad);
    }
}
