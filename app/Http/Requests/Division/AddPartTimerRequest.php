<?php

namespace App\Http\Requests\Division;

use App\Models\Member;
use App\Support\HandleRules;
use Illuminate\Foundation\Http\FormRequest;

class AddPartTimerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->member();

        return $member === null || $this->user()->can('managePartTime', $member);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'exists:members,clan_id'],
            'handles'   => ['nullable', 'array'],
            ...HandleRules::forDivision($this->route('division')),
        ];
    }

    public function member(): ?Member
    {
        return Member::where('clan_id', $this->input('member_id'))->first();
    }
}
