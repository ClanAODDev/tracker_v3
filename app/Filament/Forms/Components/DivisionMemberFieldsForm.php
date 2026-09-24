<?php

namespace App\Filament\Forms\Components;

use App\Enums\DivisionMemberFieldType;
use App\Models\Member;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class DivisionMemberFieldsForm
{
    public const FIELD = 'custom_fields';

    /**
     * @return array<int, TextInput|Select>
     */
    public static function schema(?Member $record): array
    {
        if (! $record?->division) {
            return [];
        }

        return $record->division->memberFields->map(
            fn ($field) => match ($field->type) {
                DivisionMemberFieldType::SELECT => Select::make(self::FIELD . '.' . $field->key)
                    ->label($field->label)
                    ->options(array_combine($field->optionList(), $field->optionList()))
                    ->native(false),
                DivisionMemberFieldType::TEXT => TextInput::make(self::FIELD . '.' . $field->key)
                    ->label($field->label)
                    ->maxLength(255),
            }
        )->all();
    }

    /**
     * @return array<string, string|null>
     */
    public static function getInitialValues(Member $member): array
    {
        return $member->customFieldValues()->all();
    }

    /**
     * Save the given field values for a member. Only keys present in $values
     * are touched — a partial array (e.g. a single field from an inline
     * editor) leaves the member's other field values untouched. To clear a
     * field, include its key with a null/empty value.
     */
    public static function saveValues(Member $member, array $values): void
    {
        $division = $member->division;

        if (! $division) {
            return;
        }

        foreach ($division->memberFields as $field) {
            if (! array_key_exists($field->key, $values)) {
                continue;
            }

            $value = $values[$field->key];
            $value = is_string($value) ? trim($value) : $value;

            if ($value === null || $value === '') {
                $member->fieldValues()->where('division_member_field_id', $field->id)->delete();

                continue;
            }

            $member->fieldValues()->updateOrCreate(
                ['division_member_field_id' => $field->id],
                ['value' => $value],
            );
        }
    }
}
