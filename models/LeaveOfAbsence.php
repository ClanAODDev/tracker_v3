<?php

class LeaveOfAbsence extends Application {

	public $member_id;
	public $date_end;
	public $reason;
	public $approved;
	public $approved_by;
	public $comment;
	public $game_id;

	static $table = 'loa';
	static $id_field = 'member_id';

	public static function findAll($game_id) {
		return self::find(array('game_id' => $game_id));
	}

	/**
	 * count number of expired leaves of absence
	 * @param  int $gid user's division id (game_id)
	 * @return int      number expired
	 */
	public static function count_expired($gid) {
		return count(self::find(array("date_end <" => 'NOW()', 'game_id' => $gid)));
	}

	/**
	 * count number of pending LOAs
	 * @param  int $gid user's division id (game_id)
	 * @return int      number pending
	 */
	public static function count_pending($gid) {
		return count(self::find(array('game_id' => $gid, 'approved' => 0)));
	}


}

