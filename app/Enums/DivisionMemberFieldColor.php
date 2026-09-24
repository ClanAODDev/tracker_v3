<?php

namespace App\Enums;

use App\Traits\EnumOptions;

enum DivisionMemberFieldColor: string
{
    use EnumOptions;

    case GRAY   = 'gray';
    case RED    = 'red';
    case ORANGE = 'orange';
    case YELLOW = 'yellow';
    case GREEN  = 'green';
    case BLUE   = 'blue';
    case VIOLET = 'violet';
    case PINK   = 'pink';
}
