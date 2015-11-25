<?php

class SubGame extends Application
{
    public $id;
    public $game;
    public $full_name;
    public $short_name;
    public $parent_division;

    public static $id_field = 'id';
    public static $table = 'subgames';

    public static function count($division_id)
    {
        return self::find_each(array('parent_division' => $division_id));
    }

    public static function get($division_id)
    {
        return self::find_each(array('parent_division' => $division_id));
    }

    public static function add()
    {
    }
    public static function remove()
    {
    }
}
