<?php

namespace App\Data;

readonly class PendingAction
{
    public function __construct(
        public string $key,
        public int $count,
        public string $url,
        public string $icon,
        public string $label,
        public string $style = 'default',
        public ?string $modalTarget = null,
    ) {}
}
