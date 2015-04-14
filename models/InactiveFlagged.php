<?php

class InactiveFlagged extends Application {
	public $member_id;
	public $flagged_by;

	static $id_field = 'member_id';
	static $table = 'inactive_flagged';

	public static function add($member_id, $flagged_by) {
		$sql = "INSERT INTO inactive_flagged VALUES ({$member_id}, {$flagged_by})";
		return Flight::aod()->sql($sql)->one();
	}

	public static function remove($member_id) {
		$sql = "DELETE FROM inactive_flagged WHERE member_id = {$member_id}";
		return Flight::aod()->sql($sql)->one();
	}
}