<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Traits\HasActivityGraph;

class UnitRepository
{
    use HasActivityGraph;

    public function getVoiceActivity(Unit $unit): array
    {
        return $this->getActivity('last_voice_activity', $unit);
    }
}
