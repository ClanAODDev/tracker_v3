<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivision extends FormRequest
{
    public function persist($division)
    {
        $division->settings()->merge(
            $this->only([
                'slack_channel'
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
            'slack_channel' => 'nullable|alpha_dash'
        ];
    }
}
