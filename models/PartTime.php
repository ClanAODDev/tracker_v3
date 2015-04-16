<?php

class PartTime extends Application {
	public $member_id;
	public $forum_name;
	public $battlelog_name;
	public $game_id;

	static $id_field = 'member_id';
	static $name = 'forum_name';
	static $table = 'part_timers';

	public static function findAll($game_id) {
		return self::find(array('game_id' => $game_id));
	}

}

