<?php

namespace App\Http\Requests\Leave;

use App\Enums\ActivityType;
use App\Models\Leave;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeave extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', [$this->route('member')]);
    }

    public function rules(): array
    {
        return [
            'end_date' => 'date|after:today',
        ];
    }

    public function persist(): void
    {
        $leave = Leave::findOrFail($this->leave_id);

        if ($this->approve_leave) {
            $leave->approver()->associate(auth()->user());
        }

        $leave->update($this->all());

        if ($this->approve_leave) {
            $leave->member->recordActivity(ActivityType::APPROVED_LEAVE);
        } else {
            $leave->member->recordActivity(ActivityType::EXTENDED_LEAVE);
        }
    }
}
