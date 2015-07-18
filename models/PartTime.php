<?php

class PartTime extends Application {
	public $member_id;
	public $forum_name;
	public $battlelog_name;
	public $game_id;

	static $id_field = 'member_id';
	static $name = 'forum_name';
	static $table = 'part_timers';

	public static function find_all($game_id) {
		return self::find(array('game_id' => $game_id));
	}

	public static function add($member_id, $date, $reason, $comment) {
		$member = Member::findByMemberId($member_id);
		$sql = "INSERT INTO loa ( member_id, date_end, reason, comment, game_id ) VALUES ( {$member_id}, '{$date}', '{$reason}', '{$comment}', {$member->game_id} )";
		Flight::aod()->sql($sql)->one();
		return array('success' => true);
	}

	public static function delete($loa_id) {
		$loa = self::find($loa_id);
		Flight::aod()->remove($loa);
		return array('success' => true);
	}

	public static function modify($params) {
		$member = new self();
		foreach ($params as $key=>$value) {
			$member->$key = $value;
		}
		$member->update($params);
	}

}

