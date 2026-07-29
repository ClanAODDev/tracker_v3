<?php

namespace App\Http\Requests\DivisionTag;

use App\Models\DivisionTag;
use Illuminate\Foundation\Http\FormRequest;

class CreateBulkTagPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign', DivisionTag::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'member-data' => 'required|string',
        ];
    }
}
