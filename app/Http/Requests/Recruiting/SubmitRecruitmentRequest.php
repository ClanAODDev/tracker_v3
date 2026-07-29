<?php

namespace App\Http\Requests\Recruiting;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;

class SubmitRecruitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'forum_name' => [
                'required',
                fn ($attr, $value, $fail) => Member::isValidForumName($value)
                    ?: $fail('Forum name cannot contain HTML special characters (< > & " \').'),
            ],
        ];
    }
}
