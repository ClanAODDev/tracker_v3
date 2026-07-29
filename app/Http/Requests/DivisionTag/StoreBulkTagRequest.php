<?php

namespace App\Http\Requests\DivisionTag;

use App\Models\DivisionTag;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign', DivisionTag::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'member_ids'   => 'required|array',
            'member_ids.*' => 'integer',
            'tags'         => 'required|array',
            'tags.*'       => 'integer|exists:division_tags,id',
            'action'       => 'required|in:assign,remove',
        ];
    }
}
