<?php

namespace App\Authorization;

use App\Enums\UnitLevel;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface UnitHierarchy
{
    public function leadershipLevel(User $user): ?UnitLevel;

    public function leads(Member $leader, Platoon|Squad|Unit $unit): bool;

    public function leadsUnitOf(Member $leader, Member $member): bool;

    public function sharesLedUnit(Member $leader, Member $member, UnitLevel $level): bool;

    public function scopeToLedUnit(Builder $members, Member $leader, UnitLevel $level): Builder;

    public function flush(): void;
}
