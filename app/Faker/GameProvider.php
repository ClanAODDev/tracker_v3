<?php


namespace App\Faker;

class GameProvider extends \Faker\Provider\Base
{
    protected static array $games = [
        'iRacing',
        'ARK',
        'Armored Warfare',
        'Battlefield',
        'Battlefront II',
        'Black Desert',
        'Floater',
        'Jedi Knight',
        'Overwatch',
        'Planetside 2',
        'Skyforge',
        'Tom Clancy',
        'Warframe',
        'War Thunder',
        'Warhammer',
        'World of Warcraft',
        'Titanfall 2',
        'Mass Effect',
        'PlayerUnknown',
        'Elite: Dangerous',
        'Destiny 2',
        'World of Warships',
        'Sea of Thieves',
        'Fortnite',
        'Bless Online',
        'Escape From Tarkov',
        'Call of Duty',
        'Fallout 76',
        'Hearts of Iron IV',
        'Anthem',
        'Apex Legends',
        'Hell Let Loose',
        'Halo',
        'Squad',
        'Valorant',
        'Conan Exiles',
    ];

    public function game(): string
    {
        return static::randomElement(static::$games);
    }
}
