<?php

namespace App\Enums;

use App\Traits\EnumOptions;

enum DivisionMemberFieldColor: string
{
    use EnumOptions;

    case GRAY   = 'gray';
    case RED    = 'red';
    case BLUE   = 'blue';
    case YELLOW = 'yellow';
    case GREEN  = 'green';
    case VIOLET = 'violet';
}
