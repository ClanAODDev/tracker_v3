<?php

class Division extends Application {

	public $id;
	public $description;
	public $short_name;
	public $full_name;
	public $subforum;
	public $short_descr;
	public $division_structure_thread;
	public $welcome_forum;
	public $primary_handle;

	static $table = 'divisions';
	static $id_field = 'id';
	static $name_field = 'short_name';

	public static function find_all() {
		return self::fetch_all();
	}

	public static function findById($id) {
		return (object) self::find($id);
	}

	public static function findByName($short_name) {
		return (object) self::find($short_name);
	}

	public static function findDivisionLeaders($gid) {
		$sql = "SELECT member.id, member.member_id, member.forum_name, rank.abbr as rank, member.battlelog_name, position.desc as position_desc FROM ".Member::$table." LEFT JOIN rank on member.rank_id = rank.id LEFT JOIN `position` ON member.position_id = position.id WHERE position_id IN (1,2) AND member.game_id = {$gid} ORDER BY position_id ASC";
		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function findGeneralSergeants($gid) {
		$sql = "SELECT member.id, member.member_id as forum_id, member.forum_name, rank.abbr as rank, position.desc as position_desc, member.battlelog_name FROM ".Member::$table." LEFT JOIN rank on member.rank_id = rank.id LEFT JOIN `position` ON member.position_id = position.id WHERE position_id = 3 AND member.game_id = {$gid} AND status_id = 1";
		$params = Flight::aod()->sql($sql)->many();
		return arrayToObject($params);
	}

	public static function findSquadLeaders($gid, $order_by_rank = false) {
		$sql = "SELECT last_activity, rank.abbr, member_id, forum_name, platoon.name, member.battlelog_name FROM ".Member::$table." LEFT JOIN platoon ON platoon.id = member.platoon_id LEFT JOIN rank ON rank.id = member.rank_id WHERE member.game_id = {$gid} AND position_id = 5";

		if ($order_by_rank) {
			$sql .= " ORDER BY member.rank_id DESC, member.forum_name ASC ";
		} else {
			$sql .= " ORDER BY platoon.id, forum_name";
		}

		$params = Flight::aod()->sql($sql)->one();
		return arrayToObject($params);

	}

	public static function countSquadLeaders($game_id) {
		$sql = "SELECT count(*) as count FROM ".Member::$table." WHERE position_id = 5 AND game_id = {$game_id}";
		$params =  Flight::aod()->sql($sql)->one();
		return $params['count'];
	}

	public static function recruitsThisMonth($game_id) {
		$sql = "SELECT count(*) as count, m.forum_name, join_date FROM ".Member::$table." m WHERE join_date >= DATE_SUB(CURRENT_DATE, INTERVAL DAYOFMONTH(CURRENT_DATE)-1 DAY) AND m.game_id = {$game_id}";
		return arrayToObject(Flight::aod()->sql($sql)->one());
	}

	public static function totalCount($game_id) {
		$sql = "SELECT count(*) as count FROM ".Member::$table." WHERE member.game_id = {$game_id} AND status_id IN (1,3,999)";
		return arrayToObject(Flight::aod()->sql($sql)->one());
	}

	public static function _create() {}
	public static function _modify() {}
	public static function _delete() {}
}