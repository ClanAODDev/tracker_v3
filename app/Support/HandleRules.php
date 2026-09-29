<?php

namespace App\Support;

use App\Models\Division;
use App\Models\Handle;
use App\Rules\HandleFormat;

class HandleRules
{
    public static function forRows(array $rows, string $prefix = 'handles'): array
    {
        $types = Handle::whereIn('id', collect($rows)->pluck('handle_id')->filter())->get()->keyBy('id');

        return collect($rows)
            ->mapWithKeys(fn ($row, $index) => [
                "{$prefix}.{$index}.value" => [new HandleFormat($types->get($row['handle_id'] ?? null))],
            ])
            ->all();
    }

    public static function forDivision(Division $division, string $prefix = 'handles'): array
    {
        return $division->handles
            ->mapWithKeys(fn (Handle $handle) => [
                "{$prefix}.{$handle->id}" => ['nullable', 'string', 'max:255', new HandleFormat($handle)],
            ])
            ->all();
    }
}
