<?php

class Status extends Application
{
    public $id;
    public $desc;

    public static $id_field = 'id';
    public static $table = 'status';

    public static function convert($id)
    {
        return self::find($id);
    }
}
