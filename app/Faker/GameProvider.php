<?php


namespace App\Faker;

class GameProvider extends \Faker\Provider\Base
{
    protected static array $games = [
        ['name' => 'iRacing', 'abbreviation' => 'ir'],
        ['name' => 'ARK', 'abbreviation' => 'ark'],
        ['name' => 'Armored Warfare', 'abbreviation' => 'aw'],
        ['name' => 'Battlefield', 'abbreviation' => 'bf'],
        ['name' => 'Battlefront II', 'abbreviation' => 'swb'],
        ['name' => 'Black Desert', 'abbreviation' => 'bdo'],
        ['name' => 'Floater', 'abbreviation' => 'floater'],
        ['name' => 'Jedi Knight', 'abbreviation' => 'jk'],
        ['name' => 'Overwatch', 'abbreviation' => 'ow'],
        ['name' => 'Planetside 2', 'abbreviation' => 'ps2'],
        ['name' => 'Skyforge', 'abbreviation' => 'sf'],
        ['name' => 'Tom Clancy', 'abbreviation' => 'tc'],
        ['name' => 'Warframe', 'abbreviation' => 'wf'],
        ['name' => 'War Thunder', 'abbreviation' => 'wt'],
        ['name' => 'Warhammer', 'abbreviation' => 'wh'],
        ['name' => 'World of Warcraft', 'abbreviation' => 'wow'],
        ['name' => 'Titanfall 2', 'abbreviation' => 'tf2'],
        ['name' => 'Mass Effect', 'abbreviation' => 'me'],
        ['name' => 'PlayerUnknown', 'abbreviation' => 'pubg'],
        ['name' => 'Elite: Dangerous', 'abbreviation' => 'ed'],
        ['name' => 'Destiny 2', 'abbreviation' => 'd2'],
        ['name' => 'World of Warships', 'abbreviation' => 'wows'],
        ['name' => 'Sea of Thieves', 'abbreviation' => 'sot'],
        ['name' => 'Fortnite', 'abbreviation' => 'fn'],
        ['name' => 'Bless Online', 'abbreviation' => 'bls'],
        ['name' => 'Escape From Tarkov', 'abbreviation' => 'eft'],
        ['name' => 'Call of Duty', 'abbreviation' => 'cod'],
        ['name' => 'Fallout 76', 'abbreviation' => 'fo76'],
        ['name' => 'Hearts of Iron IV', 'abbreviation' => 'hoi4'],
        ['name' => 'Anthem', 'abbreviation' => 'ath'],
        ['name' => 'Apex Legends', 'abbreviation' => 'al'],
        ['name' => 'Hell Let Loose', 'abbreviation' => 'hll'],
        ['name' => 'Halo', 'abbreviation' => 'ha'],
        ['name' => 'Squad', 'abbreviation' => 'sq'],
        ['name' => 'Valorant', 'abbreviation' => 'val'],
        ['name' => 'Conan Exiles', 'abbreviation' => 'ce'],
    ];

    public function game(): array
    {
        return static::randomElement(static::$games);
    }
}
