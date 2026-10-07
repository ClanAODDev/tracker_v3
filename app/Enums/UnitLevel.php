<?php

namespace App\Enums;

enum UnitLevel: int
{
    case Platoon = 1;
    case Squad   = 2;

    public static function forDepth(int $depth, int $deepest): self
    {
        return $depth < $deepest || $deepest === 1 ? self::Platoon : self::Squad;
    }
}
