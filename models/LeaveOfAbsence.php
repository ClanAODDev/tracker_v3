<?php

class LeaveOfAbsence extends Application {

	public $id;
	public $member_id;
	public $date_end;
	public $reason;
	public $approved;
	public $approved_by;
	public $comment;
	public $game_id;

	static $table = 'loa';
	static $id_field = 'id';

	public static function hasLOA($member_id) {
		return self::find(array('member_id' => $member_id));
	}

	public static function findById($id) {
		return self::find($id);
	}

	public static function find_all($game_id) {
		return self::find_each(array('game_id' => $game_id, 'approved' => 1));
	}

	public static function count_active($game_id) {
		return count(self::find(array('game_id' => $game_id, 'approved' => 1)));
	}

	public static function count_expired($gid) {
		return count(self::find(array("date_end <" => date('Y-m-d H:i:s'), 'game_id' => $gid)));
	}

	public static function find_expired($gid) {
		return self::find_each(array("date_end <" => date('Y-m-d H:i:s'), 'game_id' => $gid));
	}

	public static function count_pending($gid) {
		return count(self::find(array('game_id' => $gid, 'approved' => 0)));
	}

	public static function find_pending($gid) {
		return self::find_each(array('game_id' => $gid, 'approved' => 0));
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

	public static function approve($loa_id, $approvingId) {
		self::modify(array('id'=>$loa_id, 'approved'=>1, 'approved_by'=>$approvingId));        
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