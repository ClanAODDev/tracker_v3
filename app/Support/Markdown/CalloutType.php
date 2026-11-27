<?php

namespace App\Support\Markdown;

enum CalloutType: string
{
    case Hero      = 'hero';
    case Note      = 'note';
    case Tip       = 'tip';
    case Important = 'important';
    case Warning   = 'warning';
    case Caution   = 'caution';

    public function defaultTitle(): ?string
    {
        return match ($this) {
            self::Hero => null,
            default    => ucfirst($this->value),
        };
    }
}
