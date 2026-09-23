<?php

namespace App\Enums;

use App\Traits\EnumOptions;

enum DivisionMemberFieldType: string
{
    use EnumOptions;

    case TEXT   = 'text';
    case SELECT = 'select';
}
