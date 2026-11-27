<?php

namespace App\Http\Requests\Unit;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;

class StoreTopLevelUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $division = $this->route('division');

        return ! $division->isFlat() && $this->user()->can('create', [Unit::class, $division]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
