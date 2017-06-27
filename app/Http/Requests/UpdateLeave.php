<?php

namespace App\Http\Requests;

use App\Leave;
use App\Member;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeave extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', [$this->route('member')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'end_date' => 'date|after:today'
        ];
    }

    public function persist()
    {
        $leave = Leave::findOrFail($this->leave_id);

        if ($this->approve_leave) {
            $leave->approver()->associate(auth()->user());
        }

        $leave->update($this->all());
    }
}
