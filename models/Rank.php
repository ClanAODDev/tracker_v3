<?php

class Rank extends Application
{
    public $id;
    public $desc;
    public $abbr;

    public static $table = 'rank';
    public static $id_field = 'id';

    public static function convert($id)
    {
        return self::find($id);
    }
}
