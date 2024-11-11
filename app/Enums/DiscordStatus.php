<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DiscordStatus: string implements HasLabel
{
    case CONNECTED = 'connected';
    case DISCONNECTED = 'disconnected';
    case NEVER_CONNECTED = 'never_connected';
    case NEVER_CONFIGURED = 'never_configured';

    public function getDescription(): ?string
    {
        return match ($this) {
            self::DISCONNECTED => 'Connected but has since disconnected',
            self::NEVER_CONNECTED => 'Never connnected to AOD Discord',
            self::CONNECTED => 'Currently connected',
            self::NEVER_CONFIGURED => 'Never configured on AOD profile',
        };
    }

    public function getLabel(): ?string
    {
        return ucwords(str_replace('_', ' ', strtolower($this->name)));
    }

    public function badge($value, $class): string
    {
        return sprintf("<span class='%s'>%s</span>", $class, $value);
    }
}
