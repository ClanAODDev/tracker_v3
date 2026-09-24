<?php

namespace App\Http\Requests\Member;

use App\Enums\DivisionMemberFieldType;
use App\Models\Handle;
use App\Models\Member;
use App\Rules\HandleFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberDetails extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        if ($this->has('handles') && ! $this->user()->can('manageHandles', $member)) {
            return false;
        }

        if ($this->has('fields') && ! $this->user()->can('manageFields', $member)) {
            return false;
        }

        return $this->user()->can('manageHandles', $member) || $this->user()->can('manageFields', $member);
    }

    public function rules(): array
    {
        /** @var Member $member */
        $member = $this->route('member');

        $rules = [];

        if ($this->user()->can('manageHandles', $member)) {
            $rules['handles']             = ['array'];
            $rules['handles.*.id']        = ['nullable', 'integer'];
            $rules['handles.*.handle_id'] = ['required', 'integer', 'exists:handles,id'];
            $rules['handles.*.value']     = ['required', 'string', 'max:255'];
            $rules['handles.*.primary']   = ['boolean'];

            foreach ($this->input('handles', []) as $index => $row) {
                $handle                            = ! empty($row['handle_id']) ? Handle::find($row['handle_id']) : null;
                $rules["handles.{$index}.value"][] = new HandleFormat($handle);
            }
        }

        if ($this->user()->can('manageFields', $member)) {
            $rules['fields'] = ['array'];

            foreach ($member->division?->memberFields ?? [] as $field) {
                $rules["fields.{$field->key}"] = $field->type === DivisionMemberFieldType::SELECT
                    ? ['nullable', 'string', Rule::in($field->optionList())]
                    : ['nullable', 'string', 'max:255'];
            }
        }

        return $rules;
    }
}
