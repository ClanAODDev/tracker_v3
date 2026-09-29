<?php

namespace App\Http\Requests\Recruiting;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitRecruitmentRequest extends FormRequest
{
    private ?Division $division = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'forum_name' => [
                'required',
                fn ($attr, $value, $fail) => Member::isValidForumName($value)
                    ?: $fail('Forum name cannot contain HTML special characters (< > & " \').'),
            ],
        ];

        if ($this->division()?->handles->isNotEmpty()) {
            return [
                ...$rules,
                'handles' => ['nullable', 'array'],
                ...$this->division()->handleRules(),
            ];
        }

        return [
            ...$rules,
            'ingame_name' => ['required', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $division = $this->division();

                if (! $division?->handles->isNotEmpty() || $this->hasAnyDivisionHandle($division)) {
                    return;
                }

                $validator->errors()->add('handles', $division->handles->count() > 1
                    ? 'Enter at least one handle: ' . $division->handles->pluck('label')->implode(', ') . '.'
                    : "A {$division->handles->first()->label} handle is required for this division.");
            },
        ];
    }

    public function messages(): array
    {
        return [
            'ingame_name.required' => 'An in-game handle is required.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }

    private function division(): ?Division
    {
        return $this->division ??= Division::with('handles')->whereSlug($this->input('division'))->first();
    }

    private function hasAnyDivisionHandle(Division $division): bool
    {
        $values = $this->input('handles', []);

        return is_array($values) && $division->handles->contains(fn ($handle) => filled($values[$handle->id] ?? null));
    }
}
