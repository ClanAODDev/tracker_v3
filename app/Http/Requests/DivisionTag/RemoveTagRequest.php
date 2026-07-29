<?php

namespace App\Http\Requests\DivisionTag;

use App\Models\DivisionTag;
use App\Policies\DivisionTagPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign', [DivisionTag::class, $this->route('member')]) ?? false;
    }

    public function rules(): array
    {
        $assignableTagIds = (new DivisionTagPolicy)->getAssignableTags($this->user())->pluck('id');

        return [
            'tag_id' => [
                'required',
                'integer',
                Rule::exists('division_tags', 'id')->where(
                    fn ($q) => $q->whereIn('id', $assignableTagIds)
                ),
            ],
        ];
    }
}
