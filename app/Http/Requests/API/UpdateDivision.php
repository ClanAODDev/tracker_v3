<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivision extends FormRequest
{
    public function persist($division)
    {
        $division->settings()->merge(
            $this->only([
                'officer_channel',
                'member_channel',
            ])
        );

        if ($this->has('division_channel')) {
            $division->update([
                'division_channel' => $this->input('division_channel'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'officer_channel'  => 'nullable|alpha_dash',
            'member_channel'   => 'nullable|alpha_dash',
            'division_channel' => ['nullable', 'regex:/^\d{17,19}$/'],
        ];
    }
}
