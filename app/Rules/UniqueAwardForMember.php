<?php

namespace App\Rules;

use App\Models\MemberAward;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (MemberAward::where('member_id', $value)
            ->where('award_id', $this->awardId)
            ->exists()) {
            $fail('This member already has this award.');
        }
    }
}
