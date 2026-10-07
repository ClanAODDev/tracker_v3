<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use App\Services\Units\UnitAssignment;
use Illuminate\Http\RedirectResponse;

class LegacyUnitRedirectController extends Controller
{
    public function __construct(private UnitAssignment $units) {}

    public function platoon(Division $division, ?Platoon $platoon): RedirectResponse
    {
        abort_if($platoon === null, 404);

        return $this->to('unit', $division, $this->units->forLegacy($platoon));
    }

    public function manage(Division $division, ?Platoon $platoon): RedirectResponse
    {
        abort_if($platoon === null, 404);

        return $this->to('unit.manage', $division, $this->units->forLegacy($platoon));
    }

    public function squad(Division $division, ?Platoon $platoon, ?Squad $squad): RedirectResponse
    {
        abort_if($squad === null, 404);

        return $this->to('unit', $division, $this->units->forLegacy($squad));
    }

    private function to(string $route, Division $division, Unit $unit): RedirectResponse
    {
        return redirect()->route($route, [$unit->division?->slug ?? $division->slug, $unit], 301);
    }
}
