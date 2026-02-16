<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait EnumOptions
{
    public static function options(): array
    {
        $cases   = static::cases();
        $options = [];
        foreach ($cases as $case) {

            $label = $case->name;

            if (Str::contains($label, '_')) {
                $label = Str::replace('_', ' ', $label);
            }

            $options[$case->value] = Str::title($label);
        }

        return $options;
    }
}
