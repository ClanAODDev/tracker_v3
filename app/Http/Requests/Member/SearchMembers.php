<?php

namespace App\Http\Requests\Member;

use Illuminate\Foundation\Http\FormRequest;

class SearchMembers extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'     => ['nullable', 'string', 'max:100'],
            'query' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function isPartial(string $term): bool
    {
        return mb_strlen(trim($term)) >= (int) config('aod.member_search.min_chars');
    }
}
