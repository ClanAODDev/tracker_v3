<?php

namespace App\Rules;

use App\Models\Award;
use App\Models\Member;
use App\Models\MemberAward;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueAwardForMember implements ValidationRule
{
    protected $awardId;

    public function __construct($awardId)
    {
        $this->awardId = $awardId;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=):PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $award = Award::find($this->awardId);

        if ($award?->repeatable && ! $award->isPartOfTieredGroup()) {
            return;
        }

        $member = Member::whereClanId($value)->first();

        if ($member && MemberAward::where('member_id', $member->id)
            ->where('award_id', $this->awardId)
            ->exists()) {
            $fail('This member already has this award.');
        }
    }
}
