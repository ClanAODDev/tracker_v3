<?php

namespace App\Http\Requests;

use App\Platoon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePlatoonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', Platoon::class);
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages()
    {
        return [
            'leader.unique' => 'Already assigned as a leader.',
            'leader.exists' => 'Member with that clan id does not exist.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'leader' => [
                'exists:members,clan_id',
                'unique:platoons,leader_id'
            ]
        ];
    }
}
