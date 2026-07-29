<?php

namespace App\Http\Requests\DivisionTag;

use App\Enums\TagVisibility;
use App\Models\DivisionTag;
use Illuminate\Foundation\Http\FormRequest;

class CreateDivisionTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DivisionTag::class) ?? false;
    }

    public function rules(): array
    {
        $validVisibilities = [TagVisibility::PUBLIC->value, TagVisibility::OFFICERS->value];

        if ($this->user()->isRole(['admin', 'sr_ldr'])) {
            $validVisibilities[] = TagVisibility::SENIOR_LEADERS->value;
        }

        return [
            'name'       => 'required|string|max:25',
            'visibility' => 'nullable|string|in:' . implode(',', $validVisibilities),
        ];
    }
}
