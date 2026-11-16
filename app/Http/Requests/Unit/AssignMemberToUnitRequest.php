<?php

namespace App\Http\Requests\Unit;

use App\Models\Member;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;

class AssignMemberToUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->member();

        if ($member === null) {
            return true;
        }

        $unitId = (int) $this->input('unit_id');

        if ($unitId === 0) {
            $current = $member->unit;

            return $current === null || $this->user()->can('update', $current->parent ?? $current);
        }

        $unit = Unit::find($unitId);

        if ($unit === null || $unit->division_id !== $member->division_id) {
            return true;
        }

        return $this->user()->can('update', $unit);
    }

    public function rules(): array
    {
        return [
            'member'  => 'required|string|exists:members,guid',
            'unit_id' => 'required|integer',
        ];
    }

    public function member(): ?Member
    {
        return Member::where('guid', $this->input('member'))->first();
    }
}
