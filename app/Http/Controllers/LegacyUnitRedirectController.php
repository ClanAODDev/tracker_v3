<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;

class LegacyUnitRedirectController extends Controller
{
    public function platoon(Division $division, int $platoon): RedirectResponse
    {
        return $this->to('unit', $division, $this->find(Unit::LEGACY_PLATOON, $platoon));
    }

    public function manage(Division $division, int $platoon): RedirectResponse
    {
        return $this->to('unit.manage', $division, $this->find(Unit::LEGACY_PLATOON, $platoon));
    }

    public function squad(Division $division, int $platoon, int $squad): RedirectResponse
    {
        return $this->to('unit', $division, $this->find(Unit::LEGACY_SQUAD, $squad));
    }

    private function find(string $type, int $legacyId): Unit
    {
        return Unit::withTrashed()
            ->where('legacy_type', $type)
            ->where('legacy_id', $legacyId)
            ->firstOrFail();
    }

    private function to(string $route, Division $division, Unit $unit): RedirectResponse
    {
        return redirect()->route($route, [$unit->division?->slug ?? $division->slug, $unit], 301);
    }
}
