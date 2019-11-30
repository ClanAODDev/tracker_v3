<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivision extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->route('division'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $clanMaxDaysInactivity = config('app.aod.maximum_days_inactive');

        return [
            'inactivity_days' => "numeric|max:{$clanMaxDaysInactivity}"
        ];
    }

    public function persist()
    {
        $this->route('division')->settings()->merge($this->all());
    }
}
