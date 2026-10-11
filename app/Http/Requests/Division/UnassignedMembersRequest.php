<?php

namespace App\Http\Requests\Division;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UnassignedMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageUnassigned', User::class);
    }

    public function rules(): array
    {
        return [
            'level' => ['nullable', 'integer'],
        ];
    }

    public function level(): int
    {
        return max(2, min($this->integer('level', 2), Division::MAX_UNIT_LEVELS));
    }
}
