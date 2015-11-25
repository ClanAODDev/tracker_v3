<?php

class Action extends Application
{
    public $id;
    public $desc;
    public $verbage;
    public $icon;

    public static $id_field = 'id';
    public static $table = 'actions';

    public static function find_all()
    {
        return self::fetch_all();
    }
}
