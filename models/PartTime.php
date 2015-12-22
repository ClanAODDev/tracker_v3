<?php

class PartTime extends Application
{
    public $id;
    public $member_id;
    public $forum_name;
    public $ingame_alias;
    public $game_id;

    public static $id_field = 'id';
    public static $name = 'forum_name';
    public static $table = 'part_timers';

    public static function find_all($game_id)
    {
        return self::find(array('game_id' => $game_id));
    }

    public static function add($params)
    {
        $pt = new self;
        $pt->member_id = $params['member_id'];
        $pt->forum_name = $params['forum_name'];
        $pt->ingame_alias = $params['ingame_alias'];
        $pt->game_id = $params['game_id'];
        $pt->save();
    }

    public static function delete($id)
    {
        $pt = self::find($id);
        Flight::aod()->remove($pt);
    }

    public static function modify($params)
    {
        $pt = new self();
        foreach ($params as $key=>$value) {
            $pt->$key = $value;
        }
        $pt->update($params);
    }
}
