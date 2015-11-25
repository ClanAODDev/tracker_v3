<?php

class MemberGame extends Application
{
    public $id;
    public $member_id;
    public $subgame_id;

    public static $id_field = 'id';
    public static $table = 'member_games';

    public static function get($member_id)
    {
        return Flight::aod()->from(self::$table)
        ->where(array('member_id' => $member_id))
        ->join('subgames', array('subgames.id' => self::$table.'.subgame_id'))
        ->select()->many();
    }

    public static function getGamesPlayed($member_id)
    {
        return Flight::aod()->from(self::$table)
        ->where(array('member_id' => $member_id))
        ->join('subgames', array('subgames.id' => self::$table.'.subgame_id'))
        ->select('subgames.short_name')->many();
    }

    public static function plays($member_id, $game)
    {
        $params = self::find(array('member_id' => $member_id, 'subgame_id' => $game));
        return is_object($params) ? true : false;
    }

    public static function add($params)
    {
        $game = new self();
        $game->member_id = $params->member_id;
        $game->subgame_id = $params->game_id;
        $game->save();
    }

    public static function delete($params)
    {
        $game = self::find($params['id']);
        self::remove($game);
    }
}


// problem currently is that there is no way to delete entries that are added, is this needed?
