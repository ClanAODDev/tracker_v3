<?php

namespace App\Rules;

use App\Enums\Position;
use App\Models\Member;
use App\Models\Unit;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HoldsNoOtherPosition implements ValidationRule
{
    /**
     * @param  array<Position>  $allowedPositions
     */
    public function __construct(
        private ?Unit $exceptUnit = null,
        private array $allowedPositions = [],
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value) {
            return;
        }

        $member = Member::find($value);

        if (! $member) {
            return;
        }

        $conflict = $this->conflictFor($member);

        if ($conflict) {
            $fail("{$member->name} is already {$conflict}. Remove them from that position first.");
        }
    }

    private function conflictFor(Member $member): ?string
    {
        $divisionLeadership = [Position::COMMANDING_OFFICER, Position::EXECUTIVE_OFFICER];

        if (in_array($member->position, $divisionLeadership, true)
            && ! in_array($member->position, $this->allowedPositions, true)) {
            return "assigned as {$member->positionLabel()}";
        }

        $platoon = Unit::where('leader_member_id', $member->id)
            ->where('depth', 1)
            ->when($this->exceptUnit, fn ($query, $except) => $query->whereKeyNot($except->getKey()))
            ->first();

        if ($platoon) {
            return "leading {$platoon->name}";
        }

        $squad = Unit::where('leader_member_id', $member->id)
            ->where('depth', '>', 1)
            ->when($this->exceptUnit, fn ($query, $except) => $query->whereKeyNot($except->getKey()))
            ->first();

        if ($squad) {
            return 'leading ' . ($squad->name ?: 'a squad');
        }

        return null;
    }
}
