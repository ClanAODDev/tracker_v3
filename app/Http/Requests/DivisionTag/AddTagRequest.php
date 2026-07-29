<?php

namespace App\Http\Requests\DivisionTag;

use App\Models\DivisionTag;
use Illuminate\Foundation\Http\FormRequest;

class AddTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign', [DivisionTag::class, $this->route('member')]) ?? false;
    }

    public function rules(): array
    {
        return [
            'tag_id' => 'required|integer|exists:division_tags,id',
        ];
    }
}
