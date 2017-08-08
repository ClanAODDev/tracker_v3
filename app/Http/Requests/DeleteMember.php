<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteMember extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('delete', [$this->route('member')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'removal-reason' => 'required'
        ];
    }

    public function persist()
    {
        // TODO: create note when removed
        $this->route('member')->resetPositionsAndAssignments();
        $this->route('member')->delete();
    }
}
