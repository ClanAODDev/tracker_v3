<?php

namespace App\Http\Requests\Settings;

use App\Support\HandleRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateIngameHandles extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'handles' => ['array'],
            ...HandleRules::forRows((array) $this->input('handles', [])),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $failedField = array_key_first($validator->errors()->messages());
        preg_match('/^handles\.(\d+)\.value$/', $failedField, $matches);

        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(),
            'index'   => isset($matches[1]) ? (int) $matches[1] : null,
        ], 422));
    }
}
