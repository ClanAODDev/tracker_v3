<?php

class RecruitingString extends Application
{
    public $id;
    public $name;
    public $string;
    public $game_id;

    public static $id_field = 'id';
    public static $name_field = 'name';
    public static $table = 'recruiting_strings';

    public static function findByName($name, $game_id)
    {
        return self::find(array('name' => $name, 'game_id' => $game_id));
    }
}
