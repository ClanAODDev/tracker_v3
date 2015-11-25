<?php

class Handle extends Application
{
    public $id;
    public $type;
    public $name;
    public $url;
    public $show_on_profile;

    public static $id_field = 'id';
    public static $name_field = 'type';
    public static $table = 'handles';

    public static function find_all()
    {
        return self::fetch_all();
    }

    public static function findByType($id)
    {
        return self::find($id);
    }

    public static function findByName($name)
    {
        return self::find($name);
    }

    public static function create($params)
    {
        $handle = new self();
        foreach ($params as $key=>$value) {
            $handle->$key = $value;
        }
        $handle->save($params);
    }

    public static function modify($params)
    {
        $handle = new self();
        foreach ($params as $key=>$value) {
            $handle->$key = $value;
        }
        $handle->update($params);
    }

    public static function delete($id)
    {
    }
}
