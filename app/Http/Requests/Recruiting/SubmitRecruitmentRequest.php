<?php

namespace App\Http\Requests\Recruiting;

use App\Models\Division;
use App\Models\Member;
use App\Rules\HandleFormat;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitRecruitmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $division = Division::whereSlug($this->input('division'))->first();

        return [
            'forum_name' => [
                'required',
                fn ($attr, $value, $fail) => Member::isValidForumName($value)
                    ?: $fail('Forum name cannot contain HTML special characters (< > & " \').'),
            ],
            'ingame_name' => ['nullable', 'string', 'max:255', new HandleFormat($division?->handle)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }
}
