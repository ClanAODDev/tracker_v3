<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivision extends FormRequest
{
    public function persist($division)
    {
        $division->settings()->merge(
            $this->only([
                'officer_channel'
            ])
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'officer_channel' => 'nullable|alpha_dash'
        ];
    }
}
